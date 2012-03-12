<h2>Resources</h2>
<p>Here you'll find a collection of experimental tools made to support the translation and interpretation of elvish texts.</p>
<h3>Tengwar to PNG</h3>
<p>Use the text field beneath to your message in Tengwar. When you click on &quot;create image&quot;, an image is generated based on the tengwar that you specified. We recommend that you save it to the computer instead of passing the URL around, as this service might be due to change in the future.</p>

<form method="get" action="#" onsubmit="return TengwarImage.generate(this.tengwarField.value);">
<input type="text" class="tengwar" name="tengwarField" style="width:100%" />
<div style="text-align:right"><input type="submit" value="Create image" /></div>
<div style="overflow:auto" id="tengwar-result"></div>
</form>