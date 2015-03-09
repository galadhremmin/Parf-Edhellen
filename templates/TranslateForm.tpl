<h2>{$title}</h2>
<p>Test.</p>

<form class="form-horizontal" data-module="translateForm">
  <div class="form-group">
    <label for="ed-translate-word" class="col-sm-2 control-label">Word</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="ed-translate-word" placeholder="mellon">
    </div>
  </div>
  <div class="form-group">
    <label for="ed-translate-tengwar" class="col-sm-2 control-label">Tengwar</label>
    <div class="col-sm-10">
      <input type="text" class="form-control tengwar" id="ed-translate-tengwar" placeholder="tjR¸5^">
    </div>
  </div>
  <div class="form-group">
    <label for="ed-translate-gloss" class="col-sm-2 control-label">Gloss</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="ed-translate-gloss" placeholder="friend">
    </div>
  </div>
  <div class="form-group">
    <label for="ed-translate-comments" class="col-sm-2 control-label">Comments</label>
    <div class="col-sm-10">
      <textarea type="text" class="form-control" id="ed-translate-comments"></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="ed-translate-source" class="col-sm-2 control-label">Sources</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="ed-translate-source">
    </div>
  </div>
  <div class="form-group">
    <label for="ed-translate-language" class="col-sm-2 control-label">Language</label>
    <div class="col-sm-10">
      <select class="form-control" id="ed-translate-language">
        {html_options options=$inventedLanguages}
      </select>
    </div>
  </div>
  <div class="form-group">
    <label for="ed-translate-type" class="col-sm-2 control-label">Class</label>
    <div class="col-sm-10">
      <select class="form-control" id="ed-translate-type">
        {html_options options=$wordClasses}
      </select>
    </div>
  </div>
  <div class="form-group">
    <label for="ed-translate-index" class="col-sm-2 control-label">Tags</label>
    <div class="col-sm-10">
      <div class="input-group">
        <input type="text" class="form-control" id="ed-translate-index">
        <div class="input-group-btn">
          <button type="submit" class="btn btn-sm btn-default" id="ed-translate-index-add">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            Add tag
          </button>
        </div>
      </div>    
      <ul class="list-group" id="ed-translate-indexes-rendered">
      </ul>
    </div>
    <input type="hidden" id="ed-translate-indexes">
  </div>
  <div class="form-group">
    <label for="ed-translate-gender" class="col-sm-2 control-label">Gender</label>
    <div class="col-sm-10">
      <select class="form-control" id="ed-translate-gender">
        {html_options options=$wordGenders}
      </select>
    </div>
  </div>
</form>
