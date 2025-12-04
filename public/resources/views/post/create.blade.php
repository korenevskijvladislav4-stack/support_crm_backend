@extends('layouts.main')
@section('content')
    <div>
        <form action="{{route('post.store')}}" method="post">
            @csrf
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input value='{{old('title')}}' type="text" name="title" class="form-control" placeholder="Title.." id="title"
                       aria-describedby="Title">
                @error('title')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" name="content" id="content" placeholder="Content.." aria-describedby="Text..">{{old('content')}}</textarea>
                @error('content')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input value='{{old('image')}}' type="text" name="image" class="form-control" placeholder="Image.." id="image"
                       aria-describedby="Title">
                @error('image')
                <p class="text-danger">{{$message}}</p>
                @enderror
            </div>
            <div>
                <label for="category" class="form-label">Category</label>
                <select name="category_id" class="form-select form-select-lg mb-3" aria-label="Category">
                    @foreach($categories as $category)
                        <option
                            {{old('category_id') == $category->id?'selected':''}}
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
                        <option value='{{$tag->id}}'>{{$tag->title}}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection
