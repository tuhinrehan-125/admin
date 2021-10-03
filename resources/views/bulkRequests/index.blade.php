@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="" style="padding: 1.2em;">
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}  
                </div>
            @endif
            <form action="{{ route('dashboard.courier.bulk.request.upload') }}" method="post" enctype='multipart/form-data'>
                @csrf
                <div>
                    <input class="custom-file-input" name="file_data" type="file">
                </div> 
                <button type="submit" class=" btn bg-gradient-green">Upload Excel File</button>
            </form>
            <a href="{{ asset('images/holister_bulk_format.xlsx') }}" download>
                <button style="float: right;" type="submit" class=" btn bg-gradient-green">Download Format</button>
            </a>
        </div>
        <span style="color: red;margin-left: 10px"><strong>Before Uploading Bulk Product Please Check Merchant already update their city, area & address. Otherwise, You can not upload bulk products</strong></span><br><br>
        
    </div>

@endsection

