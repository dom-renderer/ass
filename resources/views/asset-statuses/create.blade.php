@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }}</h1>
        <div class="container mt-4">

            <form method="POST" action="{{ route('asset-statuses.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger"> * </span> </label>
                    <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="Enter title" required>

                    @if ($errors->has('title'))
                        <span class="text-danger text-left">{{ $errors->first('title') }}</span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label for="color" class="form-label">Color <span class="text-danger"> * </span> </label>
                    <input style="width: 100%;" type="color" name="color" class="form-control form-control-color" id="color" value="{{ old('color', '#000000') }}" title="Choose your color" required>
                    
                    @if ($errors->has('color'))
                        <span class="text-danger text-left">{{ $errors->first('color') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Type <span class="text-danger"> * </span></label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="type1" value="1" {{ old('type') == '1' ? 'checked' : '' }} checked>
                        <label class="form-check-label" for="type1">
                            Deployable
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="type2" value="2" {{ old('type') == '2' ? 'checked' : '' }}>
                        <label class="form-check-label" for="type2">
                            Undeployable
                        </label>
                    </div>

                    @if ($errors->has('type'))
                        <span class="text-danger text-left">{{ $errors->first('type') }}</span>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('asset-statuses.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection