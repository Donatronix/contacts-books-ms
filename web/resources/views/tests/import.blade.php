@extends('layout.layout')

@section('content')
    <div class="container">

        <form method="post" action="" enctype="multipart/form-data">



            <div class="form-group">
                <label for="contacts">Загрузить контакты</label>
                <input type="file" class="form-control-file" id="contacts" name="contacts">
            </div>

            <button type="submit" class="btn btn-primary">Send</button>

        </form>

    </div>
@endsection
