@extends('layouts.client')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 style="color: #9ac6ff" class="mb-0">Vazifalarni qo'shish</h3>
        @if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('User'))
            <a href="{{ route('assignments.index',['year' => request('year'), 'month' => request('month')]) }}" class="btn btn-outline-light">Orqaga</a>
        @endif
    </div>
    <form action="{{ route('assignments.store', ['year' => request('year'), 'month' => request('month')]) }}" method="POST" enctype="multipart/form-data">
        @csrf
            <div class="form-group col-md-6">
                <label for="name">Бажарилган топшириқ номи: </label>
                <textarea name="name" id="name" class="form-control" cols="30" rows="4" style="background-color: #282a3a; color: #fff;"></textarea>
            </div>

            <div class="form-group col-md-6 mt-3">
                <label for="who_from">Ким томонидан берилди: </label>
                <textarea name="who_from" id="who_from" class="form-control" cols="30" rows="4" style="background-color: #282a3a; color: #fff;"></textarea>
            </div>

            <div class="form-group col-md-6 mt-3">
                <label for="file">Fayl (Faqat ushbu formatdagi fayllarni qo'shish mumkin: jpg,jpeg,png,pdf,doc,docx,xlsx,zip) </label>
                <input type="file" class="form-control" id="file" name="file" >
            </div>

            <div class="form-group col-md-2 mt-3">
                <label for="date">Sana: </label>
                <input type="date" class="form-control" id="date" name="date" style="background-color: #282a3a; color: #fff;">
            </div>

            <div class="form-group col-md-6 mt-3">
                <label for="who_hand">Кимга топширилди: </label>
                {{-- <input type="text" class="form-control" id="who_hand" name="who_hand"> --}}
                <textarea name="who_hand" id="who_hand" class="form-control" cols="30" rows="4" style="background-color: #282a3a; color: #fff;"></textarea>
            </div>

            <div class="form-group col-md-6 mt-3">
                <label for="people">Лойиҳадаги ижрочилар ва ҳиссалар: </label>
                {{-- <input type="text" class="form-control" id="people" name="people"> --}}
                <textarea name="people" id="people" class="form-control" cols="30" rows="4" style="background-color: #282a3a; color: #fff;"></textarea>
            </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary" ><i class="fas fa-paper-plane"></i> Jo'natish</button>
        </div>
    </form>
@endsection
