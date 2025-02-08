<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Brian2694\Toastr\Facades\Toastr;

class OcrController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $check = $this->redirectIfPlanExpired(\Auth::user());
            // Check Permissions
            $permission = $this->redirectIfAuthorized(\Auth::user());

            if ($permission == 'denied') {
                abort(401);
            } else {
                if ($check == 'expired') {
                    return redirect()->route('renew-subscription');
                } else {
                    return $next($request);
                }
            }
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexOcr()
    {
        return view('ocr.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function storeOcr(Request $request)
    {
        dd($request->all());
        if ($request->hasFile('myfile')) {
            $file          = $request->file('myfile');
            $extension     = $file->getClientOriginalExtension();
            $filename      = 'ocr-' . time() . '.' . $extension;
            $size = $request->file('myfile')->getSize();

            if($extension != "pdf" && $extension != "png" && $extension != "jpg") {
                Toastr::error(trans('Please select pdf | png | jpg files only!'));
                return back();
            }

            if ($size > 5000000) {
                Toastr::error(trans('Selected file is too big!'));
                return back();
            }

            $file->move(uploadsDir('ocr'), $filename);
            $target = uploadsDir('ocr') . $filename;
            $response = $this->uploadToApi($target, $request);

            if ($response['error'] == 0) {
                return view('ocr.index')->with('response', $response);
            } else {
                Toastr::error(trans('OCR server is not responding'));
                return back();
            }
        }
    }

    public function uploadToApi($file, $request) {
        $apikey = 'K84893998588957';
        $url    = 'https://api.ocr.space/parse/image';
        $fields = array(
            'apikey'                => $apikey,
            'language'              => $request->language,
            'isOverlayRequired'     => 'false',
            'isCreateSearchablePdf' => ($request->fileOpt == 'pdf') ? 'true' : 'false',
            'file'                  => new \CURLFile($file)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);

        $return = array();
        if (isset($response['IsErroredOnProcessing']) && $response['IsErroredOnProcessing'] == '') {
            if ($request->fileOpt == 'pdf' && $response['SearchablePDFURL'] != '') {
                $return['link'] = $response['SearchablePDFURL'];
                $return['error'] = 0;

            } elseif ($request->fileOpt == 'txt') {
                $file = 'CaseWiseOCR-' . time() . '.txt';
                $content = isset($response['ParsedResults'])
                && isset($response['ParsedResults'][0])
                && isset($response['ParsedResults'][0]['ParsedText']
                ) ? $response['ParsedResults'][0]['ParsedText'] : '';

                File::put(uploadsDir() . $file, $content);
                $return['link']  = uploadsDir() . $file;
                $return['error'] = 0;
            }
        } else {
            $return['link']  = '';
            $return['error'] = 1;
        }

        return $return;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
