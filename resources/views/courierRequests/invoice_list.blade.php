@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
         <form> 
            <div class="col-md-12">
                <div class="row filter-row"> 
                    
                    <div class="col-sm-6 col-md-3">
                        <div class="form-group form-focus">
                            <label class="focus-label">Merchant Name</label>
                            <select class="select select2-hidden-accessible form-control" data-select2-id="1" tabindex="-1" aria-hidden="true" name="merchantname">
                                <option value="">Select Merchant Name</option>
                                    @foreach($users=\App\Models\User::where('type','merchant')->orwhere('type','individual')->select('id','name')->orderBy('name')->get() as $user)
                                        <option @if(request()->merchantname == $user->id) selected @endif value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-md-2">
                        <label class="focus-label"></label>
                        <button class="btn btn-success btn-block"> Search </button>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Invoice Generate List</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <div class="table-responsive">
                <table id="example" class="display table table-bordered" style="width:100%">
                    <thead> 
                    <tr>
                        <th scope="col">Invoice Number</th>
                        <th scope="col">Merchant Name</th>
                        <th scope="col">Merchant Email</th>
                        <th scope="col">Merchant Phone</th>
                        <th scope="col">Merchant Address</th> 
                        <th scope="col">Action</th> 
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $invoice->merchant_name }}</td>
                            <td>{{ $invoice->merchant_email }}</td>
                            <td>{{ $invoice->merchant_phone }}</td>
                            <td>{{ $invoice->merchant_address }}</td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('dashboard.invoice.show.list',$invoice->id) }}">Vew</a>
                                <a class="btn btn-sm btn-primary" href="{{ route('dashboard.invoice.email.send',$invoice->id) }}">Sent Email</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
            {{ $invoices->links() }}



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
        pageLength: 200,
        
        lengthMenu: [ [10, 25, 50,100,200], [10, 25, 50,100,200] ],
        dom: 'lBf',
        buttons: [
            'excel'
        ]
    } );
} );
</script>

<script type="text/javascript">
$(document).ready(function() {
        $('.select-all').on('click', function() {
            var checkAll = this.checked;
            $('input[type=checkbox]').each(function() {
                this.checked = checkAll;
            });
        });
    });
</script>


@endpush
