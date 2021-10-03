<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\IssueList;
use App\Models\CourierRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $issues = Issue::orderBy('id','desc')->get();
        $data['issues'] = $issues; 

        /*foreach ($issues as $issue) { 
            $isue['merchant_view'] = '1';
            Issue::where('id',$issue->id)->update($isue);        
        }*/

        return view('support.index',$data);
    }

    public function create()
    {
        $data['courier_requests'] = CourierRequest::where('customer_id',Auth::user()->id)->orderBy('id','asc')->select('id','tracking_id')->get();
        return view('support.create',$data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $issues['courier_id'] = $request->courier_id;
        $issues['title'] = $request->title;
        $issues['status'] = '0';
        $issues['added_by'] = Auth::user()->id;
        $issues['merchant_id'] = Auth::user()->id;
        $issues['merchant_view'] = '1';
        $list_id = Issue::create($issues)->id;

        $issue_list['issue_id'] = $list_id;
        $issue_list['description'] = $request->description;
        $issue_list['added_by'] = Auth::user()->id;
        $issue_list['merchant_seen'] = '1';

        IssueList::create($issue_list);

        session()->flash('message','Issue created successfully');
        return redirect()->route('dashboard.support');
    }

    public function show($id)
    {
        $isue['admin_view'] = '1';
        Issue::where('id',$id)->update($isue);

        $data['issue'] = Issue::find($id);
        $data['issue_lists'] = IssueList::where('issue_id',$id)->get();
        return view('support.show',$data);

    }


    public function list_store(Request $request,$id)
    {
        $request->validate([
            'description' => 'required',
        ]);
        $issue_list['issue_id'] = $id;
        $issue_list['description'] = $request->description;
        $issue_list['added_by'] = Auth::user()->id;
        $issue_list['merchant_seen'] = '0';
        IssueList::create($issue_list);

        $issues['status'] = !empty($request->status)?$request->status:'1';
        $issues['merchant_view'] = '0';
        $issues['admin_view'] = '1';
        $issues['supervisior_view'] = '0';
        Issue::where('id',$id)->update($issues);

        session()->flash('message','Submit successfully');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function destroy(Issue $issue)
    {
        //
    }
    
    public function supports_list(){
        $issues = Issue::where('merchant_id',Auth::user()->id)->select('id','courier_id','merchant_id','title','status','added_by','merchant_view','created_at')->get();
        $response = [
            'list'=> $issues
        ];
        return response()->json($response, 200);
    }

    public function supports_store(Request $request){
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $issues['courier_id'] = $request->courier_id;
        $issues['title'] = $request->title;
        $issues['status'] = '0';
        $issues['added_by'] = Auth::user()->id;
        $issues['merchant_id'] = Auth::user()->id;
        $issues['merchant_view'] = '1';
        $list_id = Issue::create($issues)->id;

        $issue_list['issue_id'] = $list_id;
        $issue_list['description'] = $request->description;
        $issue_list['added_by'] = Auth::user()->id;
        $issue_list['merchant_seen'] = '1';

        IssueList::create($issue_list);

        $response = [
            'msg'=> "Submit Successfully"
        ];
        return response()->json($response, 200);
    }

    public function supports_details($id){
        $isue['merchant_view'] = '1';
        Issue::where('id',$id)->update($isue);

        $issue = Issue::select('id','courier_id','merchant_id','title','status','added_by','merchant_view','created_at')->find($id);
        $issue_lists = IssueList::where('issue_id',$id)->select('id','issue_id','description','created_at','added_by')->with('added')->get();

        $response = [
            'issue'=> $issue,
            'list'=> $issue_lists
        ];
        return response()->json($response, 200);
    }

    public function supports_details_store(Request $request,$id){
        $request->validate([
            'description' => 'required',
        ]);
        $issue_list['issue_id'] = $id;
        $issue_list['description'] = $request->description;
        $issue_list['added_by'] = Auth::user()->id;
        $issue_list['merchant_seen'] = '1';
        IssueList::create($issue_list);

        $issues['status'] = '0';
        $issues['merchant_view'] = '1';
        $issues['admin_view'] = '0';
        $issues['supervisior_view'] = '0';
        Issue::where('id',$id)->update($issues);

        $response = [
            'msg'=> "Submit Successfully"
        ];
        return response()->json($response, 200);
    }
    
}
