<h2>{$operation}&nbsp;definition</h2>
<p>Test.</p>

<form class="form-horizontal" data-module="translateForm" action="#" method="post">
  <div class="alert alert-danger hidden" role="alert" id="ed-translate-error-alert">
    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
    There were validation errors. Please review the highlighted fields below.
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Word information</h3>
    </div>
    <div class="panel-body">
      <div class="form-group">
        <label for="ed-translate-language" class="col-sm-2 control-label">Language</label>
        <div class="col-sm-10">
          <select class="form-control" id="ed-translate-language">
            <option value="0">Select one...</option>
            {html_options options=$inventedLanguages selected=$orig_language}
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="ed-translate-word" class="col-sm-2 control-label">Word</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="ed-translate-word" placeholder="mellon" value="{htmlentities($orig_word)}">
        </div>
      </div>
      <div class="form-group">
        <label for="ed-translate-translation" class="col-sm-2 control-label">Gloss</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="ed-translate-translation" placeholder="friend" value="{htmlentities($orig_translation)}">
        </div>
      </div>
      <div class="form-group">
        <label for="ed-translate-comments" class="col-sm-2 control-label">Comments</label>
        <div class="col-sm-10">
          <textarea type="text" class="form-control" id="ed-translate-comments">{htmlentities($orig_comments)}</textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="ed-translate-source" class="col-sm-2 control-label">Sources</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="ed-translate-source" value="{htmlentities($orig_source)}">
        </div>
      </div>
      <div class="form-group">
        <label for="ed-translate-etymology" class="col-sm-2 control-label">Etymology</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="ed-translate-etymology" value="{htmlentities($orig_etymology)}">
        </div>
      </div>

    </div>
  </div>
    
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Senses &mdash; associated indexes</h3>
    </div>
    <div class="panel-body">
      <div class="form-group">
        <label for="ed-translate-index" class="col-sm-2 control-label">Senses</label>
        <div class="col-sm-10">
          <div class="input-group">
            <input type="text" class="form-control" id="ed-translate-index">
            <div class="input-group-btn">
              <button type="submit" class="btn btn-sm btn-default" id="ed-translate-index-add">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                Add sense
              </button>
            </div>
          </div>    
          <ul class="list-group" id="ed-translate-indexes-rendered">
          </ul>
        </div>
        <input type="hidden" id="ed-translate-indexes" value="{htmlentities($orig_indexes)}">
      </div>
    </div>
  </div>
    
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Additional information</h3>
    </div>
    <div class="panel-body">
      <div class="form-group">
        <label for="ed-translate-tengwar" class="col-sm-2 control-label">Tengwar</label>
        <div class="col-sm-10">
          <input type="text" class="form-control tengwar" id="ed-translate-tengwar" value="{$orig_tengwar}">
        </div>
      </div>
      <div class="form-group">
        <label for="ed-translate-type" class="col-sm-2 control-label">Class</label>
        <div class="col-sm-10">
          <select class="form-control" id="ed-translate-type">
            <option value="0">unset</option>
            {html_options options=$wordClasses selected=$orig_type}
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="ed-translate-gender" class="col-sm-2 control-label">Gender</label>
        <div class="col-sm-10">
          <select class="form-control" id="ed-translate-gender">
            {html_options options=$wordGenders selected=$orig_gender}
          </select>
        </div>
      </div>
    </div>
  </div>
  <div class="text-right">
    <input type="button" class="btn btn-default btn-cancel" type="button" value="Cancel">
    <input type="submit" class="btn btn-default" type="submit" value="Save">
  </div>
  <input type="hidden" id="ed-translate-phonetic" value="{htmlentities($orig_phonetic)}" />
  <input type="hidden" id="ed-translate-id" value="{$id}" />
  <input type="hidden" id="ed-translate-senseID" value="{$senseID}" />
</form>
