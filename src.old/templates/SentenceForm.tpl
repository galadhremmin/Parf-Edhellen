<h2>{$operation}&nbsp;sentence</h2>

<form class="form-horizontal" data-module="sentenceForm" action="#" method="post">
  <section class="panel panel-default">
    <header class="panel-heading">
      <h3 class="panel-title">Basic information</h3>
    </header>
    <div class="panel-body">
      <div class="form-group">
        <label for="ed-sentence-language" class="col-sm-2 control-label">Language</label>
        <div class="col-sm-10">
          <select class="form-control" id="ed-sentence-language">
            <option value="0">Select one...</option>
            {html_options options=$inventedLanguages selected=$sentence->language->id}
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="ed-sentence-full" class="col-sm-2 control-label">Sentence</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="ed-sentence-full" placeholder="Teitho hí i thíw dhín" value="{htmlentities($sentence->sentence)}">
        </div>
      </div>
      <div class="form-group">
        <label for="ed-sentence-source" class="col-sm-2 control-label">Sources</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="ed-sentence-source" value="{htmlentities($sentence->source)}">
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <div class="checkbox">
            <label>
              <input type="checkbox" id="ed-sentence-canonical" value="1"> Canonical
            </label>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="panel panel-default">
    <header class="panel-heading">
      <h3 class="panel-title">Detailed information</h3>
    </header>
    <div class="panel-body">
      <div class="container-fluid ed-sentence-fragments">
        <div class="row ed-sentence-template">
          <div class="col-sm-2 no-padding"><span class="ed-sentence-[[index]]-fragment">[[fragment]]</span></div>
          <div class="col-sm-10">
            <div class="form-group">
              <label for="ed-sentence-[[index]]-comments">Comments</label>
              <textarea id="ed-sentence-[[index]]-comments" class="form-control" rows="2" placeholder="comments (optional)">[[comments]]</textarea>
            </div>
            <div class="form-group">
              <label for="ed-sentence-[[index]]-tengwar">Tengwar</label>
              <input type="text" class="form-control tengwar" id="ed-sentence-[[index]]-tengwar" value="[[tengwar]]">
            </div>
            <div class="form-group">
              <label for="ed-sentence-[[index]]-translationID">Word</label>
              <p>
                <em>[[translationID]]</em>
                <a href=""><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Find word</a>
                <input type="hidden" class="form-control" id="ed-sentence-[[index]]-translationID" value="[[translationID]]">
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <input type="hidden" value="{htmlentities(json_encode($sentence->fragments))}" id="ed-sentence-definitions"/>
</form>