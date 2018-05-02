<?php

namespace App\Http\Controllers;

use App\Libraries\Utilities;
use App\Travel;
use App\TravelFile;
use App\TravelCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class TravelController extends Controller
{
    //

    public function __construct()
    {
        return $this->middleware('auth');
    }

    public function index(Request $request){
        $method = $request->isMethod('post');
        if($method){
            //delete trip/tour
            Travel::find($request->travel_id)->delete();
            return redirect()->route('travels');
        }else{
            $user_id = Auth::user()->id;
            $travels = DB::select("select tc.category as category, t.created_at as created, t.header, t.id as travel_id, ts.definition as status, t.start_date, t.end_date, t.details, t.deadline, t.single_price,
                    t.couple_price, t.uri as uri from travels as t, trip_categories as tc, travel_status as ts where
                    t.category_id = tc.id and t.status_id = ts.id and t.user_id = '$user_id' and t.deleted_at IS NULL
            ");
            //dd($travels);
            return view('travels.index', compact('travels'));
        }

    }
    public function download_file($uri){
        $queries = TravelFile::where('uri', $uri)->get();
        //dd($queries[0]->new_name);
        $file_name = $queries[0]->new_name;
        if(isset($file_name)){
            $pathToFile = public_path()."/uploads/files/".$file_name;
            return response()->file($pathToFile);
        }else{
            return back()->withErrors("File no longer exists!");
        }
    }
    public function update(Request $request, $id){
        $method = $request->isMethod('post');
        if($method){
            //update
            $validator = Validator::make($request->all(), [
                'header' => 'required',
                'category_id' => 'required',
                'start_date' => 'required|date_format:d/m/Y',
                'end_date' => 'required|date_format:d/m/Y',
                'details' => 'required|max:100',
                'deadline' => 'required|date_format:d/m/Y',
                'single_price' => 'required|numeric',
                'couple_price' => 'sometimes|numeric',
                'files' => 'file|mimes:jpg,bmp,png,jpeg|max:500000'
            ]);

            if($validator->fails()){
                return back()->withErrors($validator)->withInput();
            }
            $start_date = strtr($request->start_date, '/', '-');
            $start = date('Y-m-d', strtotime($start_date));
            //
            $end_date = strtr($request->end_date, '/', '-');
            $end =  date('Y-m-d', strtotime($end_date));

            $deadline =  strtr($request->deadline, '/', '-');
            $dead = date('Y-m-d', strtotime($deadline));
            $travel_id = $request->travel_id;

            $travel = DB::table('travels')
                        ->where('id', $travel_id)
                        ->update([
                            'header' => $request->header,
                            'category_id' => $request->category_id,
                            'start_date' => $start,
                            'end_date' => $end,
                            'details' => $request->details,
                            'deadline' => $dead,
                            'single_price' => $request->single_price,
                            'couple_price' => $request->couple_price,
                            'status_id' => 1
                        ]);
            //if($travel){
                //$travel_id = $travel->id;
                $file = $request->file('files');
                if(isset($file)){
                    $file_extension = $file->getClientOriginalExtension();
                    $new_name = base64_encode(date('Y-m-d H:i:s') . $file->getClientOriginalName()) . '.' . $file_extension;
                    $destinationPath = 'uploads/files';
                    $file->move($destinationPath, $new_name);
                    $filename = $destinationPath . '/' . $new_name;
                   // $uri = Utilities::generateFolderRef();

                    try{
                        DB::table('travel_files')
                            ->where('travel_id', $travel_id)
                            ->update([
                                'original_file_name' => $file->getClientOriginalName(),
                                'new_name' => $new_name
                            ]);
                        //dd("done");
                    }catch (Exception $e){
                        dd($e->getMessage());
                    }
                }

           // }
            $request->session()->flash('success', 'Your trip/tour has been updated!');
            return redirect()->route('edit_travels', ['id' => $id]);
        }else{
            $travels = Travel::where('uri', $id)->first();
            //dd($travels);
            $categories = TravelCategory::all();
            return view('travels.edit', compact('travels', 'categories'));
        }
    }
    public function create(Request $request){
        $method = $request->isMethod('post');
        if($method){
            //process
            $validator = Validator::make($request->all(), [
               'header' => 'required',
               'category_id' => 'required',
               'start_date' => 'required|date_format:d/m/Y',
                'end_date' => 'required|date_format:d/m/Y',
                'details' => 'required|max:100',
                'deadline' => 'required|date_format:d/m/Y',
                'single_price' => 'required|numeric',
                'couple_price' => 'sometimes|numeric',
                'img_url' => 'required'
                //'files' => 'required|file|mimes:jpg,bmp,png,jpeg|max:500000'
            ], [
                'files.required' => 'A promotional image is needed!'
            ]);
            if($validator->fails()){
                return back()->withErrors($validator)->withInput();
            }
            $start_date = strtr($request->start_date, '/', '-');
            $start = date('Y-m-d', strtotime($start_date));
            //
            $end_date = strtr($request->end_date, '/', '-');
            $end =  date('Y-m-d', strtotime($end_date));

            $deadline =  strtr($request->deadline, '/', '-');
            $dead = date('Y-m-d', strtotime($deadline));
            //create record
            $uri = Utilities::generateFolderRef();
            $travel = Travel::create([
                'header' => $request->header,
                'user_id' => Auth::user()->id,
                'category_id' => $request->category_id,
                'start_date' => $start,
                'end_date' => $end,
                'details' => $request->details,
                'deadline' => $dead,
                'single_price' => $request->single_price,
                'couple_price' => $request->couple_price,
                'status_id' => 1,
                'uri' => $uri,
                'img_url' => $request->img_url
            ]);

            if($travel){
                $travel_id = $travel->id;
                $file = $request->file('files');
                if(isset($file)){
                    $file_extension = $file->getClientOriginalExtension();
                    $new_name = base64_encode(date('Y-m-d H:i:s') . $file->getClientOriginalName()) . '.' . $file_extension;
                    $destinationPath = 'uploads/files';
                    $file->move($destinationPath, $new_name);
                    $filename = $destinationPath . '/' . $new_name;
                    $uri = Utilities::generateFolderRef();
                    TravelFile::create([
                        'travel_id' => $travel_id,
                        'original_file_name' => $file->getClientOriginalName(),
                        'uri' => $uri,
                        'new_name' => $new_name
                    ]);
                }
                //
                //dd("done");
                return redirect()->route('travels');
            }

        }else{
            $categories = TravelCategory::all();
            return view('travels.create', compact('categories'));
        }
    }
}
