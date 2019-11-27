@extends('layouts.app')

@section('header')
    Профиль пользователя {{ $user->name }}
@endsection

@section('content')
    @guest
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-8">
                <div class="card border-danger">
                    <div class="card-body">
                        <p>
                            Для простмотра профиля {{ $user->name }}, пожалуйста, пройдите процедуру 
                            <a href="{{ route('register') }}">регистрации</a>
                            или 
                            <a href="{{ route('login') }}">войдите</a>
                            в свой профиль
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endguest
    @auth
    <div class="container">
        <div class="row justify-content-center">
            <div class="card-group col-sm-12">
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-header bg-secondary text-white text-center big-text">
                        Регистрационная информация
                    </div>
                    <div class="card-body">
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Псевдоним</p>
                            </div>
                            <div class="col-sm-4">
                                <p>{{ $user->name }}</p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Имя</p>
                            </div>
                            <div class="col-sm-4">
                                <p>{{ $user->firstname ?? '--' }}</p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Фамилия</p>
                            </div>
                            <div class="col-sm-4">
                                <p>{{ $user->lastname ?? '--'}}</p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>E-mail</p>
                            </div>
                            <div class="col-sm-8">
                                <p>{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Пол</p>
                            </div>
                            <div class="col-sm-8">
                                <p>{{ $user->sex ?? '--' }}</p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Дата рождения</p>
                            </div>
                            <div class="col-sm-8">
                                <p>
                                    @if ($user->birth_day && $user->birth_month && $user->birth_year)
                                        {{ $user->birth_day }} - {{ $user->birth_month }} - {{ $user->birth_year }}
                                    @else 
                                         --
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Страна</p>
                            </div>
                            <div class="col-sm-8">
                                <p>{{ $user->country ?? '--' }}</p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Город</p>
                            </div>
                            <div class="col-sm-8">
                                <p>{{ $user->city ?? '--' }}</p>
                            </div>
                        </div>
                        <div class="row big-text">
                            <div class="col-sm-4">
                                <p>Дата регистрации</p>
                            </div>
                            <div class="col-sm-8">
                                <p>{{ $user->created_at }}</p>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                @if($user == $currentUser)
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white text-center big-text">
                            Функции
                        </div>
                        <div class="card-body">
                            <div class="row big-text">
                                <a href="{{ route('tasks.create') }}" class='text-dark'>Создать задачу</a>
                            </div>
                            <div class="row big-text">
                                <a href="{{ route('users.edit_password', $user) }}" class='text-dark'>Изменить пароль</a>
                            </div>
                            <div class="row big-text">
                                <a href="{{ route('users.edit', $user) }}" class='text-dark'>Редактировать профиль</a>
                            </div>
                            <div class="row big-text">
                                <a href="{{ route('users.destroy', $user) }}" data-confirm="Вы уверены?" data-method="delete" class='text-dark'>Удалить профиль</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>


    @endauth
@endsection