@extends('home')
@section('taisan')
    @foreach ($taisan as $item)
        <h3>{{$item->ten_ts}}</h3>
    @endforeach
@endsection