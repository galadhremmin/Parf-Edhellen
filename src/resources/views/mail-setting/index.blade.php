@extends('_layouts.default')

@section('title', 'Dashboard - Mail notifications')
@section('body')

<h1>Mail notifications</h1>
{!! Breadcrumbs::render('mail-setting.index') !!}

<p>
    What should <em>{{ config('app.name') }}</em> notify you about? A notification is raised by an event, 
    which is something that we believe might be relevant to you, and something you would have noticed, had you been looking. 
    By agreeing to be notified, you agree to receive e-mails from us. Your e-mail address is <em>{{ $email }}</em>.
</p>

<form method="post" action="{{ route('mail-setting.store') }}">
    {{ csrf_field() }}

    @foreach ($events as $event)
    <div class="checkbox">
        <label>
            <input type="checkbox" name="{{ $event }}" value="1" {{ $settings->getAttribute($event) !== 0 ? 'checked' : '' }}>
            {{ trans('mail-settings.'.$event) }}
        </label>
    </div>
    @endforeach

    <div class="text-right">
      <div class="btn-group" role="group">
        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Save changes</button>
      </div>
    </div>
</form>

@if ($overrides->count() > 0)
<h2>Exceptions</h2>
<p>
    The following objects have been disabled by you. You are not notified of events associated 
    with these objects, regardless of your settings above.
</p>

<table class="table table-striped">
<thead>
<tr>
<th>Date</th>
<th>Description</th>
</tr>
</thead>
<tbody>
@foreach ($overrides as $override)
<tr>
    <td class="date">{{ $override->updated_at ?: $override->created_at }}</td> 
    <td>{{ $override->entity_type }}</td>
</tr>   
@endforeach
</tbody>
</table>
@endif

@endsection
