@extends('layouts.blank')
@section('content')
<div class="row">
    <div class="col-md-4 p-4 border mt-2">
        <form method="post">
            @csrf
            <fieldset>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">email delivered</label>
                    <input type="number" class="form-control form-control-sm col-md-6" placeholder="total email" name="ed" value="<?= (isset($pdata))? $pdata->ed : '' ?>">
                </div>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">email opened</label>
                    <input type="number" class="form-control form-control-sm col-md-6" placeholder="email opened" name="eo" value="<?= (isset($pdata))? $pdata->eo : '' ?>">
                </div>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">email clicked</label>
                    <input type="number" class="form-control form-control-sm col-md-6" placeholder="email clicked" name="ec" value="<?= (isset($pdata))? $pdata->ec : '' ?>">
                </div>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">email replied</label>
                    <input type="number" class="form-control form-control-sm col-md-6" placeholder="email replied" name="er" value="<?= (isset($pdata))? $pdata->er : '' ?>">
                </div>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">total call</label>
                    <input type="number" class="form-control form-control-sm col-md-6" placeholder="total call" name="ct" value="<?= (isset($pdata))? $pdata->ct : '' ?>">
                </div>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">call received</label>
                    <input type="number" class="form-control form-control-sm col-md-6" placeholder="call received" name="cr" value="<?= (isset($pdata))? $pdata->cr : '' ?>">
                </div>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">total work call</label>
                    <input type="number" class="form-control form-control-sm col-md-6" placeholder="total work call" name="cwt" value="<?= (isset($pdata))? $pdata->cwt : '' ?>">
                </div>
                <div class="pb-1 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">work call received</label>
                    <input type="number" class="form-control-sm form-control col-md-6" placeholder="work call receive" name="cwr" value="<?= (isset($pdata))? $pdata->cwr : '' ?>">
                </div>
                <div class="pb-2 form-group row m-0">
                    <label class="text-capitalize text-right col-md-6 pr-1" style="margin:2px 0 0 0">Disposition</label>
                    <select xcname="d" class="form-control-sm form-control col-md-6">
                        <option value="">Select Disposition</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->stage  }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-md btn-primary float-right btn-sm">Calculate</button> 
                @if(isset($g))
                    <a href="{{ url('/test-set') }}" class="btn btn-md btn-success btn-sm">Back</a>
                @endif
            </fieldset>
        </form>
        
    </div>
    <div class="col-md-8">
        @if(isset($g))
        <div class="mt-2">
            <table class="table table-striped table-bordered">
                <tr>
                    <th style="width:40%">Data Set Group</th>
                    <td>{{ $g }}
                </tr>
                <tr>
                    <th>Mark Label</th>
                    <td> {{ $label }} </td>
                </tr>
                <tr>
                    <th>Score (Out of 100)</th>
                    <td> {{ $score }} </td>
                </tr>
            </table>
        </div>
        @endif
    </div>

@endsection