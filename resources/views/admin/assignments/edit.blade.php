@extends('layouts.admin')

@section('content')
    <div class="mt-4">
        <h3 class="mb-3 mx-2">Topshiriqqa comment yozish</h3>

        <form action="{{ route('assignment.updateComment', $assignment->id) }}" method="POST">
            @csrf
            <div class="form-group col-md-6">
                <textarea name="comment" id="summernote" rows="6" class="form-control">{{ old('comment', $assignment->comment) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Saqlash</button>
            <a href="{{ route('assignment.user', $assignment->user_id) }}" class="btn btn-secondary mt-3">Orqaga</a>
        </form>
    </div>
@endsection
