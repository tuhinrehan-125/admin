@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <form> 
            <div class="col-md-12">
                <div class="row filter-row"> 
                    
                    <div class="col-sm-6 col-md-2"> 
                        <div class="form-group form-focus">
                            <label class="focus-label">Merchant ID</label>
                            <input type="text" class="form-control " name="merchant_id" value="{{ request()->merchant_id }}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-2"> 
                        <div class="form-group form-focus"> 
                            <label class="focus-label">Merchant Name</label>
                            <input type="text" class="form-control " name="name" value="{{ request()->name }}">
                        </div> 
                    </div> 
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus"> 
                            <label class="focus-label">Merchant Email</label>
                            <input type="text" class="form-control " name="email" value="{{ request()->email }}">
                        </div> 
                    </div> 
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus"> 
                            <label class="focus-label">Merchant Phone</label>
                            <input type="text" class="form-control " name="phone" value="{{ request()->phone }}">
                        </div> 
                    </div> 
                    
                    <div class="col-sm-6 col-md-4">
                        <label class="focus-label"></label>
                        <button class="btn btn-success btn-block"> Search </button>
                    </div>

                </div>
            </div>
        </form>
        
        <div class="card-body">
            @if(session('message')) 
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Merchant Table</p>
            <div class="table-responsive">
                <table id="example" class="display table table-bordered" style="width:100%">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th> 
                        <th scope="col">Phone</th>
                        <th scope="col">Type</th>   
                        <th scope="col">Special Merchant</th>   
                        <th scope="col">Preferred Method</th> 
                        <th scope="col">Number</th> 
                        @if(Auth::user()->type == "admin")
                        <th scope="col" class="text-center">Action</th> 
                        @endif
                    </tr>
                    </thead> 
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            <td>{{$user->phone}}</td>
                            <td>{{ucfirst($user->type)}}</td>
                            <td @if($user->speical) style="background:green" @endif>
                                @if(Auth::user()->type == "admin")
                                    <a type="button" class="btn btn-sm btn-primary" href="{{ route('dashboard.special.merchant.yes',[$user->id,1]) }}">Yes</a>
                                    <a type="button" class="btn btn-sm btn-danger" href="{{ route('dashboard.special.merchant.no',[$user->id,0]) }}">No</a>
                                @else
                                    @if($user->speical == "1") Yes @else No @endif
                                @endif
                            </td>
                            
                            
                            <td>
                                @if($user->preferred_method == "bkash")
                                    BKash
                                @elseif($user->preferred_method == "nagad")
                                    Nagad
                                @elseif($user->preferred_method == "rocket")
                                    Rocket
                                @elseif($user->preferred_method == "cash")
                                    Cash
                                @elseif($user->preferred_method == "bank")
                                    <span style="color: red"><b>Bank</b></span>
                                @else
                                    {{ "Not Available" }}
                                @endif
                            </td>
                            <td>
                                @if($user->preferred_method == "bkash")
                                    {{ $user->bkash_no }}
                                @elseif($user->preferred_method == "nagad")
                                    {{ $user->nagad_no }}
                                @elseif($user->preferred_method == "rocket")
                                    {{ $user->rocket_no }}
                                @elseif($user->preferred_method == "bank")
                                    {{ $user->bank_ac_no }}<br>
                                    {{ $user->bank_name }}
                                @else
                                    {{ "Not Available" }}
                                @endif
                            </td>
                            @if(Auth::user()->type == "admin")
                            <td>
                                <div class="d-flex" style="justify-content: space-evenly;">
                                    
                                    <a href="{{ route('dashboard.user.edit',$user->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                   
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $users->links() }}
            </div>
        </div>
    </div>

@endsection
@push('custom-css')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
<link href="https://administration.holisterbd.com/public/assets/css/bootstrap-toggle.min.css" rel="stylesheet">

@endpush
@push('scripts')
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
<script src="https://administration.holisterbd.com/public/assets/js/bootstrap-toggle.min.js"></script>
<script>
    $(document).ready(function() {
    $('#example').DataTable( {
        pageLength: 20,
        "pagingType": "full_numbers",
        lengthMenu: [ [20], [20] ],
        dom: 'l',
        buttons: [
            'excel'
        ]
    } );
} );
</script>
<script>
  $(function() {
    $('.toggle-class').change(function() {
        var status = $(this).prop('checked') == true?1:0; 
        var user_id = $(this).data('id'); 
         
        $.ajax({
            type: "GET",
            dataType: "json",
            url: 'merchant/special/request/',
            data: {'status': status, 'user_id': user_id},
            success: function(data){
              console.log(data.success)
            }
        });
    })
  })
</script>
@endpush
