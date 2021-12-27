@extends('layouts.layout')

@section('title', 'Beranda')

@section('content')
    <div class="wrapper wrapper-content animated fadeInRight">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center m-t-lg">
                            @if(session('message'))
                            <div class="alert alert-{{session('message')['status']}}">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                {{ session('message')['desc'] }}
                            </div>
                            @endif
                            <h1>
                                Welcome in Bensco Project
                            </h1>
                            <small>
                                (Version 2.0)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
    
@endsection
@push('scripts')

@endpush