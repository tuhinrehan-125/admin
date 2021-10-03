<nav class="sidenav navbar navbar-vertical  fixed-left  navbar-expand-xs navbar-light bg-white" id="sidenav-main">
    <div class="scrollbar-inner">
        <!-- Brand -->
        <div class="sidenav-header  align-items-center">
            <a class="navbar-brand" href="javascript:void(0)">
                <img src="{{asset('assets/vector_files/Black.svg')}}" class="navbar-brand-img" alt="...">
            </a> 
        </div>
        <div class="navbar-inner">
            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
                <!-- Nav items --> 
                <ul class="navbar-nav">
                    @if(Auth::user()->type == "admin")
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard']) ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="ni ni-tv-2 text-primary"></i> 
                            <span class="nav-link-text">Dashboard</span>
                        </a>  
                    </li>

                    <li class="nav-item"> 
                        <a class="nav-link {{ Request::routeIs(['dashboard.user.create']) ? 'active' : '' }}" href="{{ route('dashboard.user.create') }}">
                            <i class="ni ni-single-02 text-orange"></i>
                            <span class="nav-link-text">Create Users</span>
                        </a>
                    </li>
                    @endif
                    @if((Auth::user()->type == "admin") || (Auth::user()->type == "hr"))
                    <li class="nav-item"> 
                        <a class="nav-link {{ Request::routeIs(['dashboard.staff']) ? 'active' : '' }}" href="{{ route('dashboard.staff') }}">
                            <i class="ni ni-single-02 text-orange"></i>
                            <span class="nav-link-text">Staff</span>
                        </a>
                    </li>
                    @endif
                    @if((Auth::user()->type == "admin") || (Auth::user()->type == "hr"))
                    <li class="nav-item"> 
                        <a class="nav-link {{ Request::routeIs(['dashboard.supervisor']) ? 'active' : '' }}" href="{{ route('dashboard.supervisor') }}">
                            <i class="ni ni-single-02 text-orange"></i>
                            <span class="nav-link-text">Supervisor</span>
                        </a>
                    </li>
                    @endif
                    @if((Auth::user()->type == "admin") || (Auth::user()->type == "hr"))
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.users']) ? 'active' : '' }}" href="{{ route('dashboard.users') }}">
                            <i class="ni ni-single-02 text-orange"></i>
                            <span class="nav-link-text">Riders</span>
                        </a>
                    </li>
                    @endif
                    @if((Auth::user()->type == "admin") || (Auth::user()->type == "marketing") || (Auth::user()->type == "care"))
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.merchant']) ? 'active' : '' }}" href="{{ route('dashboard.merchant') }}">
                            <i class="ni ni-single-02 text-orange"></i>
                            <span class="nav-link-text">Merchant</span>
                        </a>
                    </li>
                    @endif
                    
                    @if(Auth::user()->type == "admin")

                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.courier.type']) ? 'active' : '' }}" href="{{ route('dashboard.courier.type') }}">
                            <i class="ni ni-palette text-yellow"></i>
                            <span class="nav-link-text">Courier Type</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.delivery.mode']) ? 'active' : '' }}" href="{{ route('dashboard.delivery.mode') }}">
                            <i class="ni ni-bullet-list-67 text-default"></i>
                            <span class="nav-link-text">Delivery Mode</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.package.type']) ? 'active' : '' }}" href="{{ route('dashboard.package.type') }}">
                            <i class="ni ni-bullet-list-67 text-default"></i>
                            <span class="nav-link-text">Package Type</span>
                        </a>
                    </li>
                    @endif
                    @if((Auth::user()->type == "admin") || (Auth::user()->type == "marketing"))
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.pricing']) ? 'active' : '' }}" href="{{ route('dashboard.pricing') }}">
                            <i class="ni ni-atom text-default"></i>
                            <span class="nav-link-text">Pricing</span>
                        </a>
                    </li>
                    @endif
                    @if((Auth::user()->type == "admin"))
                    
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.city']) ? 'active' : '' }}" href="{{ route('dashboard.city') }}">
                            <i class="ni ni-map-big text-warning"></i>
                            <span class="nav-link-text">City</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.area']) ? 'active' : '' }}" href="{{ route('dashboard.area') }}">
                            <i class="ni ni-pin-3 text-success"></i>
                            <span class="nav-link-text">Area</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.branch']) ? 'active' : '' }}" href="{{ route('dashboard.branch') }}">
                            <i class="ni ni-tag text-info"></i>
                            <span class="nav-link-text">Hub</span>
                        </a>
                    </li>
                    @endif


                    
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs(['dashboard.password']) ? 'active' : '' }}" href="{{ route('dashboard.password') }}">
                            <i class="fa fa-key text-info"></i>
                            <span class="nav-link-text">Password Change</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="text-align: center;">
                            @csrf
                            <button class="btn btn-sm btn-neutral">Logout</button>
                        </form>  
                    </li>
                    
                    
                    

                </ul>

            </div>
        </div>
    </div>
</nav>
@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@endpush
