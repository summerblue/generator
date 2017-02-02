@if (count($errors) > 0)
    <div class="ui icon error message">
      <i class="remove icon"></i>
      <div class="content">
        <div class="header">There were some problems with your input.</div>
        <p>Fix the issues listed below before trying again.</p>
        <ul class="list">
          @foreach ($errors->all() as $error)
              <li><i class="remove icon"></i> {{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
@endif
