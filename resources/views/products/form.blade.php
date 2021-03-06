@extends('layouts.card')

@section('card-body')

    @php
        $route_name = current(explode('.', \Illuminate\Support\Facades\Route::currentRouteName()));
    @endphp

    <script language="JavaScript">

        function tipologiaOnChange(type) {

            $typeElementValue = $('#type').find('option:selected').val().toLowerCase();

            switch ($typeElementValue) {
                case 'fead':

                    $('#kg')
                        .attr('disabled', false)
                        .attr('readonly', false);

                    $('#amount')
                        .attr('disabled', false)
                        .attr('readonly', false);

                    break;

                case 'fead no':

                    $('#kg')
                        .attr('disabled', true)
                        .attr('readonly', true);

                    $('#amount')
                        .attr('disabled', false)
                        .attr('readonly', false);

                    break;
            }

            return false;

        }

        window.addEventListener('load', function () {

            @if(isset($product->id))
                tipologiaOnChange('{{ $product->type }}');
            @endif

        });

    </script>

    <form action="{{ isset($product->id) ? route('products.update', $product->id) : route('products.store') }}" method="post">

        @csrf

        <div class="row">
            <div class="col-7">

                <h2>Scheda prodotto</h2>

                <div class="row">
                    <div class="col">

                        <div class="form-group">
                            <label for="cod">Codice</label>
                            <input type="text"
                                   class="form-control disabled"
                                   id="cod"
                                   name="cod"
                                   placeholder="Codice"
                                   @if(isset($product->id))
                                   value="{{ $product->cod }}"
                                   @endif
                                   @if(isset($cod))
                                   value="{{ $cod }}"
                                @endif>
                        </div>

                    </div>
                    <div class="col">

                        <div class="form-group">
                            <label for="name">Tipo</label>
                            <select class="form-control"
                                    id="type"
                                    name="type"
                                    onchange="tipologiaOnChange('')">

                                <option value="">Seleziona Tipologia</option>

                                @foreach($type_array as $type)
                                    <option value="{{ $type }}"
                                            @if(isset($product->type) && $product->type == $type)
                                            selected
                                        @endif>{{ $type }}</option>
                                @endforeach

                            </select>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-8">

                        <div class="form-group">
                            <label for="name">Nome</label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   placeholder="Nome"
                                   @if(isset($product->id))
                                   value="{{ $product->name }}"
                                @endif>
                        </div>

                    </div>
                    <div class="col">

                        <div class="form-group text-center">
                            <label for="name">Punti</label>
                            <input type="text"
                                   class="form-control text-center"
                                   id="points"
                                   name="points"
                                   placeholder="Punti prodotto"
                                   @if(isset($product->id))
                                   value="{{ $product->points }}"
                                @endif>
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Descrizione</label>
                    <textarea name="description"
                              id="description"
                              cols="30"
                              rows="6"
                              class="form-control">@if(isset($product->id)){{ $product->description }}@endif</textarea>
                </div>

                <div class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="monitoring_buy"
                           name="monitoring_buy"
                           @if(isset($product->monitoring_buy) && $product->monitoring_buy == 'on')
                           checked
                        @endif>
                    <label class="custom-control-label" for="monitoring_buy">
                        <small>
                            In cassa, al momento dell'acquisto, mostra se l'articolo ?? gi?? stato acquistato.
                            <br>
                            Verr?? mostrata una piccola icona prima del nome prodotto.
                        </small>
                    </label>
                </div>

                <div class="row">
                    <div class="col">

                        <div class="form-group">
                            <label for="kg"></label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Kg.</div>
                                </div>

                                <input type="text"
                                       class="form-control text-center"
                                       id="kg"
                                       name="kg"
                                       placeholder="es. 0.5"
                                       @if(isset($product->id))
                                       value="{{ $product->kg }}"
                                       @endif
                                       @if(!isset($product->id))
                                       disabled readonly
                                    @endif>

                            </div>

                        </div>

                    </div>

                    <div class="col">

                        <div class="form-group">
                            <label for="amount"></label>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Q.t??</div>
                                </div>

                                <input type="text"
                                       class="form-control text-center"
                                       id="amount"
                                       name="amount"
                                       placeholder="es. 2"
                                       @if(isset($product->id))
                                       value="{{ $product->amount }}"
                                       @endif
                                       @if(!isset($product->id))
                                       disabled readonly
                                    @endif>

                            </div>

                        </div>

                    </div>
                </div>

                <div class="text-center">
                    <small>
                        Queste sono le quantit?? che vengono scalata al momento dell'acquisto.
                    </small>
                </div>

                <br>

                <div class="text-right">

                    <a href="javascript: history.go(-1);" class="btn btn-secondary">Annulla</a>
                    <input type="submit" class="btn btn-success" value="@if(isset($product->id)) Modifica @else Inserisci @endif">

                </div>

            </div>

            <div class="col">

                <h2>Presenti in magazzino</h2>

                <table class="table table-success table-borderless">
                    <thead>
                    <tr>
                        <th class="w-50 text-center">Kg.</th>
                        <th class="w-50 text-center">Q.t??</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="text-center">
                            <h2>
                                @if(isset($product->kg_total))
                                {{ $product->kg_total == null ? '/' : $product->kg_total }}
                                @endif
                            </h2>
                        </td>
                        <td class="text-center">
                            <h2>
                                @if(isset($product->amount_total))
                                {{ $product->amount_total == null ? '/' : $product->amount_total }}
                                @endif
                            </h2>
                        </td>
                    </tr>
                    </tbody>
                </table>

                @if(isset($product->store))

                    <br>

                    <h2>Movimentazioni (ultime 10)</h2>

                    <table class="table table-sm table-hover table-striped table-borderless rounded">
                        <thead>
                        <tr>
                            <th>Data</th>
                            <th class="text-center">Utente</th>
                            <th class="text-center">Cliente</th>
                            <th class="text-center">Ordine</th>
                            <th class="text-center">Kg.</th>
                            <th class="text-center">Q.t??</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($product->store as $movimento)

                            <tr class="@if($movimento->amount <= 0) table-danger @else table-success @endif">
                                <td>
                                    <small>
                                        {{ date('d/m/Y - H:i:s', strtotime($movimento->date)) }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if(isset($movimento->user->name))
                                    {{ $movimento->user->name }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(isset($movimento->customer->cod))
                                    {{ $movimento->customer->cod }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(isset($movimento->order->reference))
                                        {{ $movimento->order->reference }}
                                    @endif
                                </td>
                                <td class="text-center">{{ $movimento->kg }}</td>
                                <td class="text-center">{{ $movimento->amount }}</td>
                            </tr>

                        @endforeach

                        </tbody>
                    </table>

                @endif

            </div>

        </div>

    </form>

@endsection
