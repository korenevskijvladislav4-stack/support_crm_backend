@extends('layouts.main')
@section('content')
    <div>
        <div>
            <a class="btn btn-primary mb-3 mt-3" href="{{route('post.create')}}">Create</a>
        </div>
        @foreach($posts as $post)
            <div><a href="{{route('post.show', $post->id)}}">
               {{$post->id}}. {{$post->title}}
                </a>
            </div>
        @endforeach
        <div class="mt-5">
            {{$posts->withQueryString()->links()}}
        </div>
    </div>
@endsection
