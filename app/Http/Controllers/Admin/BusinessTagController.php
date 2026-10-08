<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessTag;
use App\Models\BusinessTagCode;
use App\Services\BusinessTagCodeService;
use App\Services\QrPngService;
use App\Services\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BusinessTagController extends Controller
{
    protected $UploadImage;
    protected $codeService;
    protected $qrPng;

    public function __construct(UploadImage $UploadImage, BusinessTagCodeService $codeService, QrPngService $qrPng)
    {
        $this->UploadImage = $UploadImage;
        $this->codeService = $codeService;
        $this->qrPng = $qrPng;
    }

    public function index()
    {
        $datas = BusinessTag::withCount('codes')->orderBy('id', 'desc')->get();
        return view('admin.BusinessTag.View', compact('datas'));
    }

    public function create()
    {
        return view('admin.BusinessTag.Create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $tag = new BusinessTag;
            $this->fill($tag, $request);
            $tag->save();
            return redirect()->route('business-tag')->with('success', 'Business Tag Created Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $data = BusinessTag::findOrFail($id);
        return view('admin.BusinessTag.Edit', compact('data'));
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules($id));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $tag = BusinessTag::findOrFail($id);
            $this->fill($tag, $request);
            $tag->save();
            return redirect()->route('business-tag')->with('success', 'Business Tag Updated Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $tag = BusinessTag::find($id);
            if (!$tag) {
                return response()->json(['status' => 404, 'message' => 'Business Tag not found.']);
            }
            $tag->delete(); // codes are removed by the cascading foreign key
            return response()->json(['status' => 200, 'message' => 'Business Tag deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Internal Server Error.']);
        }
    }

    /**
     * Generate N unique BT + 8 digit codes for a tag.
     */
    public function generateCodes($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        $tag = BusinessTag::find($id);
        if (!$tag) {
            return response()->json(['status' => 'failed', 'message' => 'Business Tag not found.'], 404);
        }

        try {
            $codes = $this->codeService->generate($tag->id, (int) $request->quantity);

            return response()->json([
                'status' => 'success',
                'message' => count($codes) . ' tag code(s) generated successfully.',
                'generated' => count($codes),
            ]);
        } catch (\Exception $e) {
            Log::error('BusinessTagController@generateCodes: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    public function codes($id)
    {
        $tag = BusinessTag::findOrFail($id);
        $codes = $tag->codes()
            ->with(['business:id,business_name', 'registeredUser:id,first_name,last_name,email,phone'])
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.BusinessTag.Codes', compact('tag', 'codes'));
    }

    public function exportCodes($id)
    {
        $tag = BusinessTag::findOrFail($id);
        $filename = 'business_tag_' . $tag->id . '_codes.csv';

        return response()->streamDownload(function () use ($tag) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tag Name', 'Reference No', 'Tag Code', 'Printable Tag Link', 'Created At']);
            $tag->codes()->orderBy('id')->chunk(1000, function ($codes) use ($out, $tag) {
                foreach ($codes as $code) {
                    fputcsv($out, [
                        $tag->name,
                        $code->reference_no,
                        $code->tag_code,
                        QrPngService::tagUrl($code->tag_code),
                        $code->created_at,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Download QR PNGs for selected codes (or every pending code with all=1).
     * One code returns a PNG, several return a ZIP of PNGs. A code's QR can only be
     * downloaded once: it is stamped with qr_downloaded_at and skipped afterwards.
     */
    public function downloadQr($id, Request $request)
    {
        $tag = BusinessTag::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'all' => 'nullable|boolean',
            'ids' => 'required_without:all|array',
            'ids.*' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => 'Select at least one tag code.'], 422);
        }

        $zipPath = null;

        try {
            $result = DB::transaction(function () use ($tag, $request, &$zipPath) {
                $query = $tag->codes()->whereNull('qr_downloaded_at')->orderBy('id')->lockForUpdate();
                if (!$request->boolean('all')) {
                    $query->whereIn('id', array_map('intval', $request->input('ids', [])));
                }
                $codes = $query->get();

                if ($codes->isEmpty()) {
                    return null;
                }

                if ($codes->count() === 1) {
                    $code = $codes->first();
                    $payload = [
                        'png' => $this->qrPng->make($code->tag_code, $code->reference_no),
                        'name' => $code->reference_no . '.png',
                    ];
                } else {
                    $zipPath = tempnam(sys_get_temp_dir(), 'qrzip');
                    $zip = new \ZipArchive;
                    if ($zip->open($zipPath, \ZipArchive::OVERWRITE) !== true) {
                        throw new \RuntimeException('Unable to create the ZIP file.');
                    }
                    foreach ($codes as $code) {
                        $zip->addFromString($code->reference_no . '.png', $this->qrPng->make($code->tag_code, $code->reference_no));
                    }
                    $zip->close();
                    $payload = [
                        'zip' => $zipPath,
                        'name' => 'business_tag_' . $tag->id . '_qr_codes.zip',
                    ];
                }

                // Only mark as downloaded once the files were built successfully.
                BusinessTagCode::whereIn('id', $codes->pluck('id'))->update(['qr_downloaded_at' => now()]);

                return $payload;
            });
        } catch (\Exception $e) {
            if ($zipPath && is_file($zipPath)) {
                @unlink($zipPath);
            }
            Log::error('BusinessTagController@downloadQr: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Unable to generate the QR code download.'], 500);
        }

        if ($result === null) {
            return response()->json([
                'status' => 'failed',
                'message' => 'The selected QR codes were already downloaded.',
            ], 422);
        }

        if (isset($result['png'])) {
            return response($result['png'], 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="' . $result['name'] . '"',
            ]);
        }

        return response()->download($result['zip'], $result['name'])->deleteFileAfterSend(true);
    }

    protected function rules($ignoreId = null): array
    {
        return [
            'name' => ['required', 'max:255', Rule::unique('business_tags', 'name')->ignore($ignoreId)],
            'origin_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
            'type_of_tag' => 'required|in:metal,plastic,magnet',
            'active' => 'required|in:0,1',
        ];
    }

    protected function fill(BusinessTag $tag, Request $request): void
    {
        $tag->name = $request->name;
        $tag->origin_price = $request->origin_price;
        $tag->selling_price = $request->selling_price;
        $tag->description = $request->description;
        $tag->type_of_tag = $request->type_of_tag;
        $tag->active = $request->active;
        if ($request->hasFile('image')) {
            $tag->image = $this->UploadImage->saveMedia($request->file('image'), Auth::id());
        }
    }
}
