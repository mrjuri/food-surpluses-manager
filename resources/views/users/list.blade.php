@extends('layouts.card')

@section('card-body')

    @php
        $route_name = current(explode('.', \Illuminate\Support\Facades\Route::currentRouteName()));
    @endphp

    @include('js.modalDelete')

    <div class="row">
        <div class="col-lg-8">
            <a class="btn btn-primary" href="{{ route('users.create') }}">Nuovo {{ __('layout.' . $route_name . '.single') }}</a>
        </div>
        <div class="col-lg-4">

            <div class="float-right">
                <form class="form-inline my-2 my-lg-0" action="{{ route($route_name) }}" method="get">

                    <input class="form-control mr-sm-2"
                           type="search"
                           placeholder="cerca {{ __('layout.' . $route_name . '.single') }}"
                           aria-label="Search"
                           name="s"
                           value="{{ $s ?? '' }}" />

                    <button class="btn btn-outline-info my-2 my-sm-0" type="submit">Cerca</button>

                </form>
            </div>

        </div>
    </div>

    <br>

    <table class="table">

        <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Moduli attivi</th>
            <th>Negozio</th>
            <th width="120px"></th>
        </tr>
        </thead>

        <tbody>

        @foreach($users as $user)

            @php
                $module_name = array();

                if (isset($user->json_modules) && $user->json_modules != 'null') {

                    $k = array_keys(json_decode($user->json_modules, true));

                    foreach ($k as $id) {
                        $module_name[] = __('layout.' . $id . '.title');
                    }

                }
            @endphp

            <tr>
                <td class="align-middle">{{ $user->name }}</td>
                <td class="align-middle">{{ $user->email }}</td>
                <td class="align-middle">
                    {{ implode(' | ', $module_name) }}
                </td>
                <td class="align-middle">
                    @if(isset($users_retails[$user->id]))
                    {{ implode(' | ', $users_retails[$user->id]) }}
                    @endif
                </td>
                <td class="text-center">

                    <div class="row no-gutters">
                        <div class="col">
                            <a href="{{ route('users.edit', $user->id) }}"
                               class="btn btn-success">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                        <div class="col">

                            <button type="button"
                                    class="btn btn-danger"
                                    data-toggle="modal"
                                    data-target="#deleteModal"
                                    data-href="{{ route('users.destroy', $user->id) }}">
                                <i class="far fa-trash-alt"></i>
                            </button>

                        </div>
                    </div>

                </td>
            </tr>

        @endforeach

        </tbody>


    </table>

@endsection

@section('paginate')

    <br>

    {{ $users->links() }}

@endsection
