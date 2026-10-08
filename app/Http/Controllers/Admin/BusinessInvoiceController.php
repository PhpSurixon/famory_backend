<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessInvoice;
use App\Models\BusinessInvoiceItem;
use App\Models\BusinessTag;
use App\Models\BusinessTagCode;
use App\Models\BusinessTagPrice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BusinessInvoiceController extends Controller
{
    /* ------------------------------------------------------------------ pages */

    public function index(Request $request)
    {
        $query = BusinessInvoice::with('business:id,business_name')->orderByDesc('id');

        if (in_array($request->status, [
            BusinessInvoice::STATUS_DRAFT, BusinessInvoice::STATUS_ISSUED,
            BusinessInvoice::STATUS_PAID, BusinessInvoice::STATUS_CANCELLED,
        ], true)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('business_id')) {
            $query->where('business_id', (int) $request->business_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }

        $invoices = $query->limit(2000)->get();
        $businesses = Business::orderBy('business_name')->get(['id', 'business_name']);

        $summary = [
            'draft' => $invoices->where('status', BusinessInvoice::STATUS_DRAFT)->count(),
            'issued_count' => $invoices->where('status', BusinessInvoice::STATUS_ISSUED)->count(),
            'issued_amount' => (float) $invoices->where('status', BusinessInvoice::STATUS_ISSUED)->sum('total'),
            'paid_count' => $invoices->where('status', BusinessInvoice::STATUS_PAID)->count(),
            'paid_amount' => (float) $invoices->where('status', BusinessInvoice::STATUS_PAID)->sum('total'),
            'cancelled' => $invoices->where('status', BusinessInvoice::STATUS_CANCELLED)->count(),
        ];

        return view('admin.BusinessInvoice.Index', compact('invoices', 'businesses', 'summary'));
    }

    public function create(Request $request)
    {
        $businesses = Business::where('is_active', 1)->orderBy('business_name')->get(['id', 'business_name']);
        $selectedBusinessId = (int) $request->input('business_id', 0);
        $invoice = null;
        $initialLines = [];

        return view('admin.BusinessInvoice.Form', compact('businesses', 'selectedBusinessId', 'invoice', 'initialLines'));
    }

    public function edit($id)
    {
        $invoice = BusinessInvoice::with('items')->findOrFail($id);

        if (!$invoice->canEdit()) {
            return redirect()->route('business-invoices.show', $invoice->id)
                ->with('error', 'Only a draft invoice can be edited.');
        }

        $businesses = Business::orderBy('business_name')->get(['id', 'business_name']);
        $selectedBusinessId = (int) $invoice->business_id;

        $codesByTag = $invoice->codes()->orderBy('id')->get(['id', 'reference_no', 'business_tag_id'])->groupBy('business_tag_id');
        $initialLines = $invoice->items->map(function ($item) use ($codesByTag) {
            $codes = $codesByTag->get($item->business_tag_id, collect());

            return [
                'business_tag_id' => (int) $item->business_tag_id,
                'tag_name' => $item->tag_name,
                'unit_price' => (float) $item->unit_price,
                'code_ids' => $codes->pluck('id')->values()->all(),
                'refs' => $codes->pluck('reference_no')->values()->all(),
            ];
        })->values()->all();

        return view('admin.BusinessInvoice.Form', compact('businesses', 'selectedBusinessId', 'invoice', 'initialLines'));
    }

    public function show($id)
    {
        $invoice = BusinessInvoice::with(['business', 'items'])->findOrFail($id);
        $refsByTag = $this->referencesByTag($invoice);

        return view('admin.BusinessInvoice.Show', compact('invoice', 'refsByTag'));
    }

    public function pdf($id)
    {
        $invoice = BusinessInvoice::with(['business', 'items'])->findOrFail($id);
        $refsByTag = $this->referencesByTag($invoice);

        $pdf = Pdf::loadView('invoice.business_invoice', compact('invoice', 'refsByTag'));

        return $pdf->download($invoice->invoice_no . '.pdf');
    }

    /* ------------------------------------------------- lookups for the form */

    /** JSON: Business Tags that still have billable codes for this business. */
    public function eligibleTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'invoice_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        $businessId = (int) $request->business_id;
        $counts = $this->eligibleCodes($businessId, $request->filled('invoice_id') ? (int) $request->invoice_id : null)
            ->select('business_tag_id', DB::raw('count(*) as c'))
            ->groupBy('business_tag_id')
            ->pluck('c', 'business_tag_id');

        $prices = BusinessTagPrice::where('business_id', $businessId)->pluck('business_price', 'business_tag_id');

        $tags = BusinessTag::whereIn('id', $counts->keys())->orderBy('name')->get(['id', 'name', 'selling_price'])
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'available' => (int) $counts[$t->id],
                'unit_price' => round((float) ($prices[$t->id] ?? $t->selling_price), 2),
            ])->values();

        return response()->json(['status' => 'success', 'tags' => $tags]);
    }

    /** JSON: the billable codes of one tag for this business (sold, and not on another invoice). */
    public function eligibleCodesList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'business_tag_id' => 'required|exists:business_tags,id',
            'invoice_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        $codes = $this->eligibleCodes((int) $request->business_id, $request->filled('invoice_id') ? (int) $request->invoice_id : null)
            ->where('business_tag_id', (int) $request->business_tag_id)
            ->orderBy('id')
            ->get(['id', 'reference_no', 'sold_at'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'reference_no' => $c->reference_no,
                'sold_at' => $c->sold_at ? $c->sold_at->format('d M Y') : null,
            ]);

        return response()->json(['status' => 'success', 'codes' => $codes]);
    }

    /* ------------------------------------------------------------ save flow */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(true), $this->messages());
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $invoice = $this->withInvoiceNumberRetry(function () use ($request) {
                return DB::transaction(function () use ($request) {
                    $business = Business::findOrFail($request->business_id);
                    $built = $this->buildItems($business, $request->input('lines'), null);
                    $issue = $request->action === 'issue';
                    $money = $this->money($built['subtotal'], $request->input('tax_percent'));

                    $invoice = BusinessInvoice::create([
                        'invoice_no' => $this->nextInvoiceNo(),
                        'business_id' => $business->id,
                        'status' => $issue ? BusinessInvoice::STATUS_ISSUED : BusinessInvoice::STATUS_DRAFT,
                        'invoice_date' => $request->invoice_date,
                        'bill_to_name' => $business->business_name,
                        'bill_to_email' => $business->email,
                        'bill_to_mobile' => $business->mobile,
                        'bill_to_address' => $request->bill_to_address,
                        'notes' => $request->notes,
                        'total_codes' => $built['total_codes'],
                        'subtotal' => $money['subtotal'],
                        'tax_percent' => $money['tax_percent'],
                        'tax_amount' => $money['tax_amount'],
                        'total' => $money['total'],
                        'created_by' => Auth::id(),
                        'issued_at' => $issue ? now() : null,
                        'issued_by' => $issue ? Auth::id() : null,
                    ]);

                    $this->saveItemsAndLinkCodes($invoice, $built);

                    return $invoice;
                });
            });

            return response()->json([
                'status' => 'success',
                'message' => $invoice->isIssued()
                    ? "Invoice {$invoice->invoice_no} issued."
                    : "Invoice {$invoice->invoice_no} saved as draft.",
                'redirect' => route('business-invoices.show', $invoice->id),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('BusinessInvoiceController@store: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(false), $this->messages());
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        try {
            $invoice = DB::transaction(function () use ($id, $request) {
                $invoice = BusinessInvoice::whereKey($id)->lockForUpdate()->firstOrFail();

                if (!$invoice->canEdit()) {
                    throw new \DomainException('Only a draft invoice can be edited.');
                }

                $business = Business::findOrFail($invoice->business_id);
                $built = $this->buildItems($business, $request->input('lines'), $invoice->id);
                $issue = $request->action === 'issue';
                $money = $this->money($built['subtotal'], $request->input('tax_percent'));

                // Codes taken off the draft become billable again
                BusinessTagCode::where('business_invoice_id', $invoice->id)
                    ->whereNotIn('id', $built['code_ids'])
                    ->update(['business_invoice_id' => null]);

                $invoice->items()->delete();

                $invoice->update([
                    'status' => $issue ? BusinessInvoice::STATUS_ISSUED : BusinessInvoice::STATUS_DRAFT,
                    'invoice_date' => $request->invoice_date,
                    'bill_to_name' => $business->business_name,
                    'bill_to_email' => $business->email,
                    'bill_to_mobile' => $business->mobile,
                    'bill_to_address' => $request->bill_to_address,
                    'notes' => $request->notes,
                    'total_codes' => $built['total_codes'],
                    'subtotal' => $money['subtotal'],
                    'tax_percent' => $money['tax_percent'],
                    'tax_amount' => $money['tax_amount'],
                    'total' => $money['total'],
                    'issued_at' => $issue ? now() : null,
                    'issued_by' => $issue ? Auth::id() : null,
                ]);

                $this->saveItemsAndLinkCodes($invoice, $built);

                return $invoice;
            });

            return response()->json([
                'status' => 'success',
                'message' => $invoice->isIssued()
                    ? "Invoice {$invoice->invoice_no} issued."
                    : "Invoice {$invoice->invoice_no} updated.",
                'redirect' => route('business-invoices.show', $invoice->id),
            ]);
        } catch (\DomainException $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('BusinessInvoiceController@update: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /* -------------------------------------------------------- status changes */

    /** Draft -> Issued. */
    public function issue($id)
    {
        return $this->changeStatus($id, function (BusinessInvoice $invoice) {
            if (!$invoice->canIssue()) {
                throw new \DomainException('Only a draft invoice can be issued.');
            }
            $invoice->update([
                'status' => BusinessInvoice::STATUS_ISSUED,
                'issued_at' => now(),
                'issued_by' => Auth::id(),
            ]);

            return "Invoice {$invoice->invoice_no} issued.";
        });
    }

    /** Issued -> Paid. */
    public function markPaid($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paid_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:' . implode(',', BusinessInvoice::PAYMENT_METHODS),
            'payment_reference' => 'nullable|string|max:100',
        ], [
            'paid_date.required' => 'Enter the date the payment was received.',
            'payment_method.required' => 'Choose how the invoice was paid.',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        return $this->changeStatus($id, function (BusinessInvoice $invoice) use ($request) {
            if (!$invoice->canMarkPaid()) {
                throw new \DomainException('Only an issued invoice can be marked as paid.');
            }
            $invoice->update([
                'status' => BusinessInvoice::STATUS_PAID,
                'paid_date' => $request->paid_date,
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'paid_by' => Auth::id(),
            ]);

            return "Invoice {$invoice->invoice_no} marked as paid.";
        });
    }

    /** Draft or Issued -> Cancelled. The codes become billable again. */
    public function cancel($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cancel_reason' => 'required|string|min:3|max:1000',
        ], [
            'cancel_reason.required' => 'Enter a reason for cancelling the invoice.',
            'cancel_reason.min' => 'Enter a reason for cancelling the invoice.',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 422);
        }

        return $this->changeStatus($id, function (BusinessInvoice $invoice) use ($request) {
            if (!$invoice->canCancel()) {
                throw new \DomainException('This invoice can no longer be cancelled.');
            }
            $invoice->update([
                'status' => BusinessInvoice::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancel_reason' => $request->cancel_reason,
            ]);
            BusinessTagCode::where('business_invoice_id', $invoice->id)->update(['business_invoice_id' => null]);

            return "Invoice {$invoice->invoice_no} cancelled.";
        });
    }

    /* ---------------------------------------------------------------- helpers */

    /** Run a status change on a locked invoice row and answer with JSON. */
    protected function changeStatus($id, callable $change)
    {
        try {
            $message = DB::transaction(function () use ($id, $change) {
                $invoice = BusinessInvoice::whereKey($id)->lockForUpdate()->firstOrFail();

                return $change($invoice);
            });

            return response()->json(['status' => 'success', 'message' => $message]);
        } catch (\DomainException $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Invoice not found.'], 404);
        } catch (\Exception $e) {
            Log::error('BusinessInvoiceController status change: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /** Codes of this business that can be billed: sold, and not on another invoice. */
    protected function eligibleCodes(int $businessId, ?int $invoiceId)
    {
        return BusinessTagCode::where('business_id', $businessId)
            ->whereNotNull('sold_at')
            ->where(function ($q) use ($invoiceId) {
                $q->whereNull('business_invoice_id');
                if ($invoiceId) {
                    $q->orWhere('business_invoice_id', $invoiceId);
                }
            });
    }

    /**
     * Check the chosen codes and work out the invoice lines from the database, never from the
     * browser. Quantity = number of codes, price = the business's saved Business Price for the tag
     * (the tag's selling price if none was saved).
     *
     * @throws \DomainException when a code cannot be billed
     */
    protected function buildItems(Business $business, array $lines, ?int $invoiceId): array
    {
        $tagIds = array_map(fn ($l) => (int) $l['business_tag_id'], $lines);
        if (count($tagIds) !== count(array_unique($tagIds))) {
            throw new \DomainException('Each Business Tag can only be added to an invoice once.');
        }

        $allIds = [];
        foreach ($lines as $line) {
            foreach ($line['code_ids'] as $codeId) {
                $allIds[] = (int) $codeId;
            }
        }
        if (count($allIds) !== count(array_unique($allIds))) {
            throw new \DomainException('A tag code was selected more than once.');
        }

        $codes = $this->eligibleCodes($business->id, $invoiceId)
            ->whereIn('id', $allIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($codes->count() !== count($allIds)) {
            throw new \DomainException(
                'Some selected tag codes can no longer be billed: they are not sold, belong to another business, or are already on another invoice. Reload and choose again.'
            );
        }

        $tags = BusinessTag::whereIn('id', $tagIds)->get()->keyBy('id');
        $prices = BusinessTagPrice::where('business_id', $business->id)->pluck('business_price', 'business_tag_id');

        $items = [];
        $subtotal = 0.0;
        $totalCodes = 0;

        foreach ($lines as $line) {
            $tag = $tags->get((int) $line['business_tag_id']);
            if (!$tag) {
                throw new \DomainException('A selected Business Tag no longer exists.');
            }

            foreach ($line['code_ids'] as $codeId) {
                if ((int) $codes[(int) $codeId]->business_tag_id !== (int) $tag->id) {
                    throw new \DomainException('A selected tag code does not belong to ' . $tag->name . '.');
                }
            }

            $quantity = count($line['code_ids']);
            $unitPrice = round((float) ($prices[$tag->id] ?? $tag->selling_price), 2);
            $lineTotal = round($quantity * $unitPrice, 2);

            $items[] = [
                'business_tag_id' => $tag->id,
                'tag_name' => $tag->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
            $subtotal += $lineTotal;
            $totalCodes += $quantity;
        }

        return [
            'items' => $items,
            'code_ids' => $allIds,
            'total_codes' => $totalCodes,
            'subtotal' => round($subtotal, 2),
        ];
    }

    protected function saveItemsAndLinkCodes(BusinessInvoice $invoice, array $built): void
    {
        foreach ($built['items'] as $item) {
            BusinessInvoiceItem::create($item + ['business_invoice_id' => $invoice->id]);
        }

        BusinessTagCode::whereIn('id', $built['code_ids'])->update(['business_invoice_id' => $invoice->id]);
    }

    protected function money(float $subtotal, $taxPercent): array
    {
        $taxPercent = round((float) $taxPercent, 2);
        $taxAmount = round($subtotal * $taxPercent / 100, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total' => round($subtotal + $taxAmount, 2),
        ];
    }

    /** BINV-2026-00001: sequential within the year. Call inside a transaction. */
    protected function nextInvoiceNo(): string
    {
        $prefix = 'BINV-' . now()->format('Y') . '-';

        $last = BusinessInvoice::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('invoice_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    /** The unique index on invoice_no is the final guard if two invoices are created at once. */
    protected function withInvoiceNumberRetry(callable $create)
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $create();
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= 3) {
                    throw $e;
                }
            }
        }
    }

    /** Reference numbers (never tag codes) of the codes on the invoice, grouped by tag. */
    protected function referencesByTag(BusinessInvoice $invoice)
    {
        return $invoice->codes()
            ->orderBy('id')
            ->get(['reference_no', 'business_tag_id'])
            ->groupBy('business_tag_id')
            ->map(fn ($codes) => $codes->pluck('reference_no')->all());
    }

    protected function rules(bool $creating): array
    {
        $rules = [
            'invoice_date' => 'required|date',
            'bill_to_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'action' => 'required|in:draft,issue',
            'lines' => 'required|array|min:1',
            'lines.*.business_tag_id' => 'required|integer|exists:business_tags,id',
            'lines.*.code_ids' => 'required|array|min:1',
            'lines.*.code_ids.*' => 'integer',
        ];

        if ($creating) {
            $rules['business_id'] = 'required|exists:businesses,id';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'business_id.required' => 'Choose a business.',
            'invoice_date.required' => 'Enter the invoice date.',
            'lines.required' => 'Add at least one Business Tag to the invoice.',
            'lines.min' => 'Add at least one Business Tag to the invoice.',
            'lines.*.code_ids.required' => 'Choose at least one tag code for every tag on the invoice.',
            'lines.*.code_ids.min' => 'Choose at least one tag code for every tag on the invoice.',
        ];
    }
}
