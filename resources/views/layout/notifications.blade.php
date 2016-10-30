@if(Session::has('success'))
    <div class="hidden" data-notification="success" data-type="success">
        <h4>Succes!</h4>
        <div class="message">{{{ Session::get('success') }}}</div>
    </div>
@endif

@if(Session::has('danger'))
    <div class="hidden" data-notification="danger" data-type="danger">
        <h4>Error!</h4>
        <div class="message">{{{ Session::get('danger') }}}</div
    </div>
@endif

@if(Session::has('warning'))
    <div class="hidden" data-notification="warning" data-type="warning">
        <h4>Warning!</h4>
        <div class="message">{{{ Session::get('warning') }}}</div
    </div>
@endif

@if(Session::has('info'))
    <div class="hidden" data-notification="info" data-type="info">
        <h4>Let op!</h4>
        <div class="message">{{{ Session::get('info') }}}</div
    </div>
@endif

@if(Session::has('error'))
    <div class="hidden" data-notification="error" data-type="error">
        <h4>Let op!</h4>
        <div class="message">{{{ Session::get('error') }}}</div
    </div>
@endif

@if(count($errors) > 0)
    <div class="hidden" data-notification="danger" data-type="danger">
        <h4>Form errors</h4>
        <div class="message">
        @foreach ($errors->toArray() as $field => $error)
            <div data-error-field="{{ $field }}">{{{ $errors->first($field) }}}</div>
        @endforeach
        </div>
    </div>
@endif