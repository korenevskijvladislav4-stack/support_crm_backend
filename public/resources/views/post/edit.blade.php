@extends('layouts.main')
@section('content')
    <div>
        <form action="{{route('post.update', $post->id)}}" method="post">
            @csrf
            @method('patch')
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" class="form-control" id="title" aria-describedby="Title"
                       value="{{$post->title}}">
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" name="content" id="content"
                          aria-describedby="Text..">{{$post->content}}</textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="text" name="image" class="form-control" id="image" aria-describedby="Title"
                       value="{{$post->image}}">
            </div>
            <div>
                <label for="category" class="form-label">Category</label>
                <select name="category_id" class="form-select form-select-lg mb-3" aria-label="Category">
                    @foreach($categories as $category)
                        <option
                            {{$category->id === $post->category_id ? 'selected':''}}
                            value="{{$category->id}}">
                            {{$category->title}}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="Tags" class="form-label">Tags</label>
                <select name="tags[]" class="form-select" multiple aria-label="Tags">
                    @foreach($tags as $tag)
                        <option
                            @foreach($post->tags as $postTag)
                            {{$tag->id === $postTag->id ? 'selected':''}}
                            @endforeach
                            value='{{$tag->id}}'
                        >{{$tag->title}}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
