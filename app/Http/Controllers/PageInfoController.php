<?php

namespace App\Http\Controllers;

use Exception;
use Flash;
use App\Models\InfoPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; 

class PageInfoController extends Controller
{
    public function index()
    {
        $infoPages = InfoPage::paginate(10);
        return view('admin.infoPage.index', compact('infoPages'));
    }

    public function show($page_url){
        $page = InfoPage::where('page_url',$page_url)->first();
        if(!$page){
            abort(404);
        }
        return view("admin.infoPage.show",compact('page'));
    }
    public function create()
    {
        return view('admin.infoPage.create');
    }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'page_name' => 'required',
    //         'page_content' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }
    //     try {
    //         if (InfoPage::where('page_name', $request->page_name)->first()) {
    //             flash()->error("Page Already exist");
    //             return redirect()->route('info-pages.create');
    //         }

    //         $pages =  new InfoPage;
    //         $pages->page_name   = $request->page_name;
    //         $pages->page_content = $request->page_content;
    //         // $pages->page_url = $this->slugify($request->page_name);
    //         $pages->page_url = Str::slug($request->page_name);
    //         $pages->save();
    //          return redirect('index')->with('key', 'Page has been created successfully!');
    //         flash()->success('Page has been created successfully!');
    //         return redirect()->route('info-pages.index');
    //     } catch (Exception $exception) {
            
    //         // flash()->error($exception->getMessage());
    //         // return redirect()->route('info-pages.create');
    //         return redirect()->route('info-pages.create')->withErrors(['error' => $exception->getMessage()]);

    //     }
    // }
    
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'page_name' => 'required',
        'page_content' => 'required',
    ]);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    try {
        $page = new InfoPage;
        $page->page_name = $request->page_name;
        $page->page_content = $request->page_content;
        $page->page_url = Str::slug($request->page_name);
        $page->save();
        
       return redirect()->route('info-pages.create')->with('success', 'Page has been created successfully!');
        
    } catch (Exception $exception) {
         return redirect()->route('info-pages.create')->with('error', $exception->getMessage());
    }
}

    public function edit($id)
    {
        $page = InfoPage::find($id);
        return view('admin.infoPage.edit', compact('page'));
    }

    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'page_name' => 'required',
    //         'page_content' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }
    //     try {
    //         if (InfoPage::where('page_name', $request->page_name)->where('_id', '<>', $id)->first()) {
    //             // flash()->error("Page Already exist");
    //             // return redirect()->route('info-pages.create');
    //              return redirect()->route('info-pages.create')->with('success', 'Page Already exist');
    //         }

    //         $pages =  InfoPage::find($id);
    //         $pages->page_name   = $request->page_name;
    //         $pages->page_content = $request->page_content;
    //         $pages->page_url = $this->slugify($request->page_name);
    //         $pages->save();
    //         // flash()->success('Page has been updated successfully!');
    //         // return redirect()->route('info-pages.index');
    //     return redirect()->route('info-pages.index')->with('success', 'Page has been updated successfully!');
    //     } catch (Exception $exception) {
    //         // flash()->error($exception->getMessage());
    //         // return redirect()->route('info-pages.edit', $id);
    //         return redirect()->route('info-pages.create')->withErrors(['error' => $exception->getMessage()]);

            
    //     }
    // }
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'page_name' => 'required',
        'page_content' => 'required',
    ]);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    try {
        // $existingPage = InfoPage::where('page_name', $request->page_name)->where('_id', '<>', $id)->first();
        // if ($existingPage) {
        //     return redirect()->route('info-pages.edit', $id)->with('error', 'Page name already exists');
        // }

if (InfoPage::where('page_name', $request->page_name)->where('id', '<>', $id)->first()) {
    return redirect()->route('info-pages.create')->with('success', 'Page Already exist');
}

        $page = InfoPage::find($id);
        if (!$page) {
            return redirect()->route('info-pages.index')->with('error', 'Page not found');
        }

        $page->page_name = $request->page_name;
        $page->page_content = $request->page_content;
         $page->page_url = Str::slug($request->page_name);
        $page->save();

        return redirect()->route('info-pages.index')->with('success', 'Page has been updated successfully!');
    } catch (Exception $exception) {
        return redirect()->route('info-pages.edit', $id)->with('error', $exception->getMessage());
    }
}

    public function destroy($id)
    {
        try {
            $page = InfoPage::find($id);
            $page->delete();
            flash()->success('Page has been deleted successfully!');
            return redirect()->route('info-pages.index');
        } catch (Exception $exception) {
            flash()->error($exception->getMessage());
            return redirect()->route('info-pages.index');
        }
    }


}
