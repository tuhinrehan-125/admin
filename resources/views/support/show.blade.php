@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul> 
                </div>
            @endif  
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">{{ $issue->title }} -  @if(!empty($issue->courier_id)) Courier Id#{{$issue->courier_id}} @endif</p>
            @foreach($issue_lists as $issue_list)
                <div class="containers @if($issue_list->added_by != Auth::user()->id) darker @endif">
                  <h4 >{{ !empty($issue_list->added->name)?$issue_list->added->name:"Not Available" }}</h4>
                  <p>{{ $issue_list->description }}</p>
                  <span class="time-right">{{ date('d M, Y h:i A',strtotime($issue_list->created_at)) }}</span>
                </div>
            @endforeach

            @if($issue->status != '3')
            <form action="{{ route('dashboard.support.ticket.list.add',$issue->id) }}" method="post">
                @csrf
                <div class="form-group">
                  <label for="example-description" class="form-control-label">Description</label>
                  <textarea rows="10" class="form-control" type="text" name="description" placeholder="Support description" id="example-description" required>{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                  <label for="example-description" class="form-control-label">Support Status</label>
                  <select class="form-control" required name="status">
                    <option value="">Select Status</option>
                    <option value="0">Pending</option>
                    <option value="1">Answered</option>
                    <option value="3">Closed</option>
                  </select>
                </div>
                
                
                <div>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
            @else
            <h4 style="color: red">Closed</h4>
            @endif
        </div>
    </div>
@endsection

@push('custom-css')
<style>
body {
  margin: 0 auto;
  max-width: 100%;
  padding: 0 20px;
}

.containers {
  border: 2px solid #dedede;
  background-color: #f1f1f1;
  border-radius: 5px;
  padding: 10px;
  margin: 10px 0;
}

.darker {
  border-color: #ccc;
  background-color: #ddd;
}

.containers::after {
  content: "";
  clear: both;
  display: table;
}

.containers img {
  float: left;
  max-width: 60px;
  width: 100%;
  margin-right: 20px;
  border-radius: 50%;
}

.containers img.right {
  float: right;
  margin-left: 20px;
  margin-right:0;
}

.time-right {
  float: left;
  color: #aaa;
}

.time-left {
  float: left;
  color: #999;
}
</style>
@endpush()

@push('scripts')


@endpush()