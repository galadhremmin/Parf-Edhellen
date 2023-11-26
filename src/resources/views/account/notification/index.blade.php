@extends('_layouts.default')

@section('title', 'Notification settings')
@section('body')

<h1>Notification settings</h1>
{!! Breadcrumbs::render('notifications.index') !!}

<p>
  What should <em>{{ config('app.name') }}</em> notify you about? A notification is raised by an event, 
  which is something that we believe might be relevant to you, and something you would have noticed, had you been looking. 
  By agreeing to be notified, you agree to receive e-mails from us. Your e-mail address is <em>{{ $email }}</em>.
</p>

<form method="post" action="{{ route('notifications.store') }}">
  {{ csrf_field() }}

  @foreach ($events as $event)
  <div class="checkbox">
    <label>
      <input type="checkbox" name="{{ $event }}" value="1" {{ $settings->getAttribute($event) !== 0 ? 'checked' : '' }}>
      @lang('account.notifications.categories.'.$event)
    </label>
  </div>
  @endforeach

  <div class="text-end">
    <div class="btn-group" role="group">
      <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
  </div>
</form>

@if ($overrides->count() > 0)
<h2>Exceptions</h2>
<p>
  You have requested that we do not notify you about changes in the following areas, regardless of your settings above.
</p>

<table class="table table-striped">
<thead>
<tr>
<th>Date</th>
<th>Description</th>
<th>Name</th>
<th></th>
</tr>
</thead>
<tbody>
@foreach ($overrides as $override)
<tr>
  <td>@date($override->updated_at ?: $override->created_at)</td> 
  <td>@lang('account.notifications.exceptions.'.$override->entity_type)</td>
  <td>{{ $override->entity->name ?: $override->entity->subject }}</td>
  <td class="text-end">
    <form method="post" action="{{ route('notifications.delete-override', ['entityType' => $override->entity_type, 'entityId' => $override->entity_id]) }}">
      @method('delete')
      @csrf
      <button type="submit" class="btn btn-secondary">Delete</button>
    </form>
  </td>
</tr>   
@endforeach
</tbody>
</table>
@endif


@endsection
