(function () {
  $(window).on('navigator.navigated', function () {
    var searchReg = /\b([A-Z]{1}[A-Za-z]*)[0-9]{0,2}[:\/]{1}([0-9]{1,3}|[IV]{1,2})/g;
    
    var i, j, m;
    var books = $('#related-books:visible');
    if (books.length < 1) {
      return;
    }
    
    var parts = $('.word-source,.word-comments');
    var links = [];
    var addedLinks = {};
    
    for (i = 0; i < parts.length; i += 1) {
      var html = parts[i].innerHTML;
      if (!html) {
        continue;
      }
      
      j = 0;
      while ((m = searchReg.exec(html)) !== null && j++ < 200) { // j < 200 is just an IE 8 stupidity safety check, to avoid infinite loops.
        if (addedLinks[m[1]]) {
          continue;
        }
        
        addedLinks[m[1]] = true;
        
        switch (m[1].toLowerCase()) {
          case 'lotr':
          case 'nam':
          case 'eo':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0345538374&asins=0345538374&linkId=53ITD3TQ75UCQLYY&show_border=false&link_opens_in_new_window=true');
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0618640150&asins=0618640150&linkId=ZNH2O3IKXYLB2ALX&show_border=false&link_opens_in_new_window=true');
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0547928211&asins=0547928211&linkId=XCMHYXT6VTTJ36PQ&show_border=false&link_opens_in_new_window=true');
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0547928203&asins=0547928203&linkId=PDJ2Y7Q4RYTLORFG&show_border=false&link_opens_in_new_window=true');
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=054792819X&asins=054792819X&linkId=JBPHXBNNEBOGSFCU&show_border=false&link_opens_in_new_window=true');
            break;
          case 'silm':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0345325818&asins=0345325818&linkId=HGDSJGS7VW55VMNE&show_border=false&link_opens_in_new_window=true');
            break;
          case 'mc':
          case 'markirya':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=026110263X&asins=026110263X&linkId=RNAASQDEJWLKKKO2&show_border=false&link_opens_in_new_window=true');
            break;
          case 'mr':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0261103008&asins=0261103008&linkId=QOY6PLEFSXPF3ZNA&show_border=false&link_opens_in_new_window=true');
            break;
          case 'lr':
          case 'etym':
          case 'fs':
          case 'co':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0345406850&asins=0345406850&linkId=3E4P6EDMQDJXXCNT&show_border=false&link_opens_in_new_window=true');
            break;
          case 'rgeo':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0007136552&asins=0007136552&linkId=AAW4NSX5HCNJIU2B&show_border=false&link_opens_in_new_window=true');
            break;
          case 'rs':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=061808357X&asins=061808357X&linkId=5ZBWPIKKL5XKEAA4&show_border=false&link_opens_in_new_window=true');
            break;
          case 'ti':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0618083588&asins=0618083588&linkId=DGOH542TBXJO6PV2&show_border=false&link_opens_in_new_window=true');
            break;
          case 'pm':
          case 'wj':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0395827604&asins=0395827604&linkId=4PHYCKXTWATG6E2L&show_border=false&link_opens_in_new_window=true');
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0261103245&asins=0261103245&linkId=EEDMTETP4TSFNKSE&show_border=false&link_opens_in_new_window=true');
            break;
          case 'letters':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0618056998&asins=0618056998&linkId=2QHNZDEW5I7O6R7R&show_border=false&link_opens_in_new_window=true');
            break;
          case 'lt':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0345375211&asins=0345375211&linkId=KBIHAX64VDHIQCKK&show_border=false&link_opens_in_new_window=true');
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=034537522X&asins=034537522X&linkId=XWZ65H5XF64YP5AB&show_border=false&link_opens_in_new_window=true');
            break;
          case 'arct':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0618512659&asins=0618512659&linkId=KHH3QOFHZ47TOPVI&show_border=false&link_opens_in_new_window=true');
            break;
          case 'vt':
            links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=qf_sp_asin_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=B002ACVRNA&asins=B002ACVRNA&linkId=BHKADJEXGCOAKJYX&show_border=false&link_opens_in_new_window=true');
            break;
        }
      }
    }
    
    // Pedin Edhellen English Royal
    links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=1447886674&asins=1447886674&linkId=LUHF7OVZPKFHMMZO&show_border=false&link_opens_in_new_window=true');
    // Quetin I Lambë Eldaiva
    links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=1447822110&asins=1447822110&linkId=I5HTBWFUFU3Q4JSM&show_border=false&link_opens_in_new_window=true');
    // A Gateway to Sindarin 
    links.push('//ws-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&OneJS=1&Operation=GetAdHtml&MarketPlace=US&source=ac&ref=tf_til&ad_type=product_link&tracking_id=elfcomparedh-20&marketplace=amazon&region=US&placement=0874809126&asins=0874809126&linkId=6YFN2C5RQDBLX3MG&show_border=false&link_opens_in_new_window=true');
    
    for (i = 0; i < links.length; i += 1) {
      links[i] = '<iframe style="width:120px;height:240px;" marginwidth="0" marginheight="0" scrolling="no" frameborder="0" src="'+links[i]+'"></iframe>';
    }
    
    books.html(links.join(''));
    
    window.setTimeout(function () {
      // detect ad blockers
      if (books.find('iframe:visible').length < 1)  {
        $('.adblocker').show();
      }
    }, 500);
  });
})();
