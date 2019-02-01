var $= jQuery.noConflict();


Array.prototype.remove = function(value) {
    var idx = this.indexOf(value);
    if (idx != -1) {
        return this.splice(idx, 1);
    }
    return false;
}



Array.prototype.in_array = function(p_val) {
	for(var i = 0, l = this.length; i < l; i++)	{
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
}






 

$( document ).ready(function() { 

 /********CONFIG*defailt collation TV*********/
 
  var defaultTVcollation = {
    'IMG':6,
  }; 


/*
    var defaultTVcollation = {
    'IMG':9
  }; */
  /********CONFIG**********/
  
  
  var pathXLS = false;
  var imageListName = Array();
  var imageListState = Array();
  var imageList = Array();
  var tocat = false;
  var typeImport = "allInSelected"; // forColLevels
  var selectAreaStart = false;
  var selectAreaEnd = false;
  var currentSheet  = 0;
  var shiftKeyDown = false;

  var selectAimg = false;
  var selectAunique = false;
  var callationIndex = false;

  var collationImageCol = false;
  
  var selectColCat1st = false;
  var selectAcolLonkImg = false;
  var selectAcolLonkImg_pos = false;
  var selectAcolLocalImg = false;
  var selectAcolLonkImg_pos = false;
  var selectColCat1st_pos = false;
  var addToPGT = false;

  var arrayCats = Array();
	var arrayImgLinks = Array();
  var arrayImgLinksLocal = Array();
	var arrayCols = Array();

  var currRowFrom = 0;
  var currRowTo = 1;
  
  var chunkSize = 100;

  var statementDload = true;






  $('#uploaded_file').change(function(){   // заменить click на change
    
   //pathXLS = '/home/u479023/lamparostov.ru/www/xls/1508746579.xls';//заглушка!!! убрать   1481281805.xls 
   //xlsImported(pathXLS); //заглушка!!! убрать
    //return;//заглушка!!! убрать
     
    var progressBar = $('#docprogress');
    var fileXLS = this.files;
    var data = new FormData();
    fileName = fileXLS[0]['name'];
    $('.file_upload mark').text(fileName);
    $('.file_upload .button').text("Изменить"); 
    
    $.each( fileXLS, function( key, value ){
      data.append( key, value );
    });
    
    $.ajax({
      url: '../assets/modules/importXLS/_action_ajax.php?uploadfiles',
      type: 'POST',
      data: data,
      cache: false,
      dataType: 'json',
      processData: false,
      contentType: false,
      xhr: function(){
        var xhr = $.ajaxSettings.xhr();
        xhr.upload.addEventListener('progress', function(evt){
          if(evt.lengthComputable) {
          var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
          progressBar.css({width: percentComplete + '%'});
          }
        }, false);
        return xhr;
      },
      success: function( respond ){
          if (respond.result){
              pathXLS=respond.path;
              xlsImported(respond.path);
          }
      }
    });
  });    
  //$('#uploaded_file').click();//заглушка!!! убрать
  











function initScrollTable() {
    $(".prepaireTable").on("scroll" , function(ex){
    //  console.log(ex);
      
      oh = ex.target.offsetHeight;
      ch = ex.target.clientHeight;
      th = $(".xlsTable").height();
      to = $(".xlsTable").offset().top;  

      statetCoord = to + (th - ch - 198);

      if (statetCoord < 300){
          
          if (statementDload){
             statementDload = false;
             //statementDload = xlsImported(pathXLS, currRowTo, false, currentSheet);
             xlsImported(pathXLS, currRowTo, false, currentSheet , true); 


          }
         
      }

    });
}






  
  function setDefaultTVcollation(){
    $.each( defaultTVcollation, function( key, value ){
      //ctrl=$(".dropableCtrl[data-idcol="+key+"]");
      ctrl = $(".dropableCtrl[data-idcol="+key+"]");
      ctrl.data("attachtv", value).attr("data-attachtv" , value);
      bgTvColor = $(".oneTv[data-tvid="+value+"]").css("background");
      ctrl.find('span')
      .css({background:bgTvColor})
      .html(value);
      

      
      
    });
  };










  $('.prepaireTable').on("click",".checkAll" , function(){ 
    $(".checkString").prop("checked",  $(this).prop("checked") )
  });
  
  
  
/*
$("label").click(function(event) {
    e.preventDefault();
    e.stopPropagation();
  });
  
$(".checkbox_px").click(function(event) {
    e.preventDefault();
    e.stopPropagation();
  });
    
   */





  //shiftKeyDown
$(window).keydown(function(event) {
  if (event.keyCode == 16) {
    shiftKeyDown = true;
    
  }  
});







$(window).keyup(function(event) {
  if (event.keyCode == 16) {
    shiftKeyDown = false;
    
  }
});






$('.prepaireTable').on("click",".checkString" , function(e){ 
  if (shiftKeyDown) {
    if (selectAreaStart !== false) {
      selectAreaEnd = $(this).data("stringid");
      setCheckArea(selectAreaStart,selectAreaEnd);
      selectAreaStart = selectAreaEnd = false; 
    }else {
      selectAreaStart = $(this).data("stringid");
      e.preventDefault();
    }  
  }else {
    selectAreaStart = $(this).data("stringid");
  }
});








  function setCheckArea(selectAreaStart,selectAreaEnd){
    if (selectAreaStart > selectAreaEnd) {
      tmp = selectAreaStart;
      selectAreaStart = selectAreaEnd;
      selectAreaEnd = tmp;
    } 
    var  firstCheckedElem = $(".checkString:checked:first").data("stringid");
    var  lastCheckedElem = $(".checkString:checked:last").data("stringid");  
    var typeCheck =  "checked";
    if (firstCheckedElem < selectAreaStart && lastCheckedElem > selectAreaEnd) {
      typeCheck =  false;
    }
    
    for (var i = selectAreaStart; i <= selectAreaEnd; i++){
      $('#srtCh_'+i).prop("checked", typeCheck);
    }
  };

  
  
  
  
  
    


  function xlsImported(path,from = 0,to = false, listIndex = 0, apptudeEnd = false){
    $.ajax({
      url: '../assets/modules/importXLS/_action_ajax.php?getXMLdata',
      type: 'POST',
      dataType: 'json',
      data: {
        pathToXLS: path,
        from:from,
        to:to, 
        listIndex : listIndex 
      },
      cache: false,
      success: function( respond ){
          breakPointsWidth = Array();
          breakPoints = Array();
          findedindex = false;
          currRowFrom = parseInt(respond.meta.from);
          if (currRowFrom == 0 ) startCnt = currRowFrom ; else startCnt = currRowFrom-1;
          currRowTo = currRowFrom + chunkSize;
          printTableNewTest(respond.data , apptudeEnd , startCnt);
          printSheetList(respond.meta.allList);
          
          $(".rightBL").fadeIn();
          //setMinMax(respond.meta);
      }
  });
}








  function printSheetList(jsonData){
    buffer = '';
    $.each( jsonData ,function(outerIndex, outerValue){
      if (currentSheet == outerIndex) {
        addsClass = "crntSheet";
      }else {
        addsClass = "";
      }
      buffer += '<span class="'+addsClass+'" data-idsheet="'+outerIndex+'"><nobr>'+outerValue+'</nobr></span> ';
    });
    $('.sheetList').empty();
    $('.sheetList').append(buffer);     
}














  function printTableNewTest(jsonData , apptudeEnd = false , startCNT = 0 ){
    var buffer = '';
    if (apptudeEnd == false)  buffer += '<table class=xlsTable>';

    var firstIteration = true;
    $.each( jsonData ,function(outerIndex, outerValue){
      
      if (firstIteration && apptudeEnd == false) {
        buffer += '<tr id="fixedROW">';
        buffer += '<td>Игн.</td>';
        buffer += '<td></td>';
        buffer += '<td>#</td>';
        buffer += '<td class="dropableCtrl idtvimg" data-idcol="IMG"><span></span></td>';
        $.each( outerValue ,function(innerIndex, innerValue){


            let tmp = $('.pxselectlistdefaulttv').clone().css({display: 'block'});



            var elementJq = $('<td/>', {
                class: 'dropableCtrl',
                id: 'drop_'+innerIndex,
                'data-idcol': innerIndex,
                click: function() {
                }
            }).append($('<span/>').append(tmp[0].outerHTML));

           // console.log(elementJq[0].outerHTML);

            buffer += elementJq[0].outerHTML;


            //buffer += '<td class="dropableCtrl" id="drop_'+innerIndex+'" data-idcol="'+innerIndex+'"><span>'+($('.pxselectlistdefaulttv').html())+'</span></td>';
        });
        buffer += '</tr>';
        
        buffer += '<tr id="fixedROW2">';
        buffer += '<td></td>';
        buffer += '<td><input type="checkbox" class="checkAll" /></td>';
        buffer += '<td>#</td>';
        buffer += '<td><span>IMG</span></td>';
        $.each( outerValue ,function(innerIndex, innerValue){
          buffer += '<td><span>'+(String.fromCharCode((65+innerIndex)))+'</span></td>';
        });
        buffer += '</tr>';
        firstIteration = false;
      }
         
      buffer += '<tr class="etlWidthed">';
      buffer += '<td><input type="checkbox" class="checkStringIgnore" id="srtChIgnore_'+(outerIndex+parseInt(startCNT))+'" data-stringidignore="'+(outerIndex+parseInt(startCNT))+'"  /></td>';
      buffer += '<td><input type="checkbox" class="checkString" id="srtCh_'+(outerIndex+parseInt(startCNT))+'" data-stringid="'+(outerIndex+parseInt(startCNT))+'"  /></td>';
      buffer += '<td>'+(outerIndex+parseInt(startCNT))+'</td>';
      buffer += '<td class="dropFirImages" data-rownum="'+(outerIndex+parseInt(startCNT))+'"></td>';
      $.each( outerValue ,function(innerIndex, innerValue){
        addsClass = '';
        if (innerIndex == 0 ) {
          //addsClass = ' class="itscolimg itsimg_'+innerValue+'"  '; //REMODE
          addsClass = ' class="itscolimg" data-valuep="itsimg_'+(innerValue.split('.')[0])+'" ';
        }else {
          addsClass = ' class="" data-valuep="itsimg_'+(innerValue.split('.')[0])+'" ';
        }
        buffer += '<td'+addsClass+'>'+innerValue+'</td>'; 
      });
      buffer += '</tr>';
    });
    if (apptudeEnd == false) {
      buffer += '</table>';
      $('.xlsTable').remove();
      $('.tableColSetter').empty();
      $('.prepaireTable').append(buffer);
    }else {
       $('.xlsTable').append(buffer);
       statementDload = true;
    } 


      for (var ig =  0 ; ig <= 2; ig++) {
        setTimeout(function(tt){
           $("#srtChIgnore_"+tt).prop("checked" , true).parent().parent().find('td').addClass("ignored");
        }, 500 * ig + 1000 , ig);
      } 

      for (var iik =  2 ; iik > 0; iik--) {
        setTimeout(function(tt){
           $("#srtChIgnore_"+tt).prop("checked" , false).parent().parent().find('td').removeClass("ignored");
        }, 2500  , iik);
      }









    initScrollTable();
    $( initDraggable );
    //setTimeout(setFixedRow,200);
    //$( setFixedRow );  
    $( setDefaultTVcollation );
}




$('body').on("change",".pxselectlistdefaulttv" , function(e){
    var sp = $(this).parent();
    var td = $(this).parent().parent();
    var type = $(this).find("option:selected").data('type');


    if (type == 'tv') {
        var id = $(this).find("option:selected").data('tvid');


        sp.data('typefield' , 'tv').attr('data-typefield' , 'tv');
        td.data('attachtv' , id).attr('data-attachtv' , id);
    }else if(type == 'flt') {
        var id = $(this).find("option:selected").data('fltid');

        sp.data('typefield' , 'filter').attr('data-typefield' , 'filter');
        td.data('attachfilter' , id).attr('data-attachfilter' , id);
    }else if (type == 'NULL'){
        sp.data('typefield' , 'false').attr('data-typefield' , 'false');
        td.data('attachfilter' , 'false').attr('data-attachfilter' , 'false');
        td.data('attachtv' , 'false').attr('data-attachtv' , 'false');

    }






});




$('.sheetList').on("click","span" , function(e){
  var listIndex = $(this).data("idsheet");
  currentSheet = listIndex;
  $('.xlsTable').remove();
  tocat= false;
  $(".selectAcat .miniAddsTx").html("Не задано");
  xlsImported(pathXLS, 0, false, listIndex);
});





  //$("html,body").css("overflow","hidden");
  
  $('body').on("click",".darkBG" , function(e){
    $(this).css({display:'none'});
    $(".treeList").css({display:'none'});
    $(".modalNotice").css({display:'none'});
  });
  
  
  $('body').on("click",".selectAcat" , function(e){
    $(".darkBG").css({display:'block'});
    $(".treeList").css({display:'block'});
  });
  
  

  
  $('body').on("change",".checkStringIgnore" , function(e){
    if( $(this).prop("checked") ){
      $(this).parent().parent().find("td").addClass("ignored");
    }else {
      $(this).parent().parent().find("td").removeClass("ignored");
    }

  });
  
  






  function printTable(jsonData){
    var buffer = '<table class=xlsTable>';
    var firstIteration = true;
    $.each( jsonData ,function(outerIndex, outerValue){
      
      if (firstIteration) {
        buffer += '<tr id="fixedROW">';
        buffer += '<td></td>';
        buffer += '<td>#</td>';
        buffer += '<td class="dropableCtrl idtvimg" data-idcol="IMG"><span></span></td>';
        $.each( outerValue ,function(innerIndex, innerValue){
          buffer += '<td class="dropableCtrl" id="drop_'+innerIndex+'" data-idcol="'+innerIndex+'"><span></span></td>';
        });
        buffer += '</tr>';
        
        buffer += '<tr id="fixedROW2">';
        buffer += '<td><input type="checkbox" class="checkAll" /></td>';
        buffer += '<td>#</td>';
        buffer += '<td><span>IMG</span></td>';
        $.each( outerValue ,function(innerIndex, innerValue){
          buffer += '<td><span>'+(String.fromCharCode((65+innerIndex)))+'</span></td>';
        });
        buffer += '</tr>';
        firstIteration = false;
      }
         
      buffer += '<tr class="etlWidthed">';
      buffer += '<td><input type="checkbox" class="checkString" id="srtCh_'+outerIndex+'" data-stringid="'+outerIndex+'"  /></td>';
      buffer += '<td>'+outerIndex+'</td>';
      buffer += '<td class="dropFirImages" data-rownum="'+outerIndex+'"></td>';
      $.each( outerValue ,function(innerIndex, innerValue){
        addsClass = '';
        if (innerIndex == 0 ) {
          //addsClass = ' class="itscolimg itsimg_'+innerValue+'"  '; //REMODE
          addsClass = ' class="itscolimg" data-valuep="itsimg_'+innerValue+'" ';
        }else {
          addsClass = ' class="" data-valuep="itsimg_'+innerValue+'" ';
        }
        buffer += '<td'+addsClass+'>'+innerValue+'</td>'; 
      });
      buffer += '</tr>';
    });
    buffer += '</table>';

    $('.xlsTable').remove();
    $('.tableColSetter').empty();
    $('.prepaireTable').append(buffer);


      for (var ig =  0 ; ig <= 3; ig++) {
        console.log("#srtChIgnore_"+ig);
        setTimeout(function(){
          console.log("#srtChIgnore_"+ig);
           $("#srtChIgnore_"+ig).prop("checked" , true);
        }, 1000 * ig);

      }

    $( initDraggable );
    //setTimeout(setFixedRow,200);
    //$( setFixedRow );  
    $( setDefaultTVcollation );
}






$( getTreeCat );

function getTreeCat() {
  $.ajax({
      url: '../assets/modules/importXLS/_action_ajax.php?buildTreeCat',
      type: 'POST',
      cache: false,
      success: function( respond ){
         // $(".treeList").html(respond);
          $(".treeList").html('<div class="treeElem"></div>');
          $(".treeList .treeElem:first").append('<span class="pickFolder" data-idcat="2">КАТАЛОГ</span>');
          $(".treeList .treeElem:first").append(respond);

      }
  });
}

 
//$(".darkBG").css({display:'block'});
//$(".modalNotice").css({display:'block'});





 $("body").on('click','.delNotUpdated.active',function(e){
    $.ajax({
        url: '../assets/modules/importXLS/_action_ajax.php?markDeletes='+tocat,
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function( respond ){
            //alert(respond);
            $(".darkBG").css({display:'block'});
            $(".modalNotice").css({display:'block'});
            $(".oneSring").html("Будет удалено "+respond.cnt+" позиций из категории \""+respond.pgt+"\" (ID "+respond.root+") ");
        }
    });
 });








 $("body").on('click','.accDelete',function(e){
    $.ajax({
        url: '../assets/modules/importXLS/_action_ajax.php?markDeletes='+tocat+'&doo=true',
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function( respond ){

        }
    });
 });














//pickFolder
 $("body").on('click','.treeElem',function(e){
   tocat = $(this).find(".pickFolder").data("idcat");
   typeImport = "allInSelected";
   pickFLD =  $(this).find(".pickFolder:first");
   $(".selectAcat .miniAddsTx").html("ID "+ pickFLD.data("idcat")); 
   $(".noticeDelepeItems").html("В категории \""+ pickFLD.text()+"\" (ID "+pickFLD.data('idcat')+")");
   //$(".dopBtn.delNotUpdated").addClass("active");

   e.stopPropagation();
   $(".darkBG").css({display:'none'});
   $(".treeList").css({display:'none'});


   
   
   $("#px_f_1").prop("checked",  true )
   //$("#px_f_2").prop("checked",  false )
 });






 $("body").on('click','.addToPgt',function(e){

  if ($(this).hasClass("active")) {
     $(this).removeClass("active");
     addToPGT = false;

  }
  $('.addToPgt').removeClass("active");
  $(this).addClass("active"); 
  addToPGT = $(this).parent().data("idcol");

 });



 $("body").on('click','.deleteThis',function(e){


    console.log('ddddddd');

     $(this).parent().data("attachtv" , false)
      .attr("data-attachtv", false)
      .data("attachfilter" , false)
      .attr("data-attachfilter", false)
      .find("span.ui-droppable")
      .data("typefield" , false)
      .attr("data-typefield", false)
      .css({background:"none"})
      .html('');

      $(this).parent().find("span:not(.ui-droppable)").remove();

  



 });





  
  
  






function initDraggable() {
  
  $('.draggableMe').draggable( {
      revert:"invalid",
      helper: "clone",
      zIndex: 999,
      start: function(event, ui) {

      },
      
      stop: function(event, ui) {
        offsetLeft = ui.position.left;
      }
      
  });
  
  $(".dropableCtrl span").droppable({
        accept:".draggableMe",
        hoverClass:"statehighlight",
        drop:function(event, ui){
          dropedElem = ui.draggable;
          
          $.each( $(".dropableCtrl") ,function(innerIndex, innerValue){
            if ($(this).data("attachtv") == dropedElem.data("tvid")) {
              $(this).data("attachtv" , false)
              .attr("data-attachtv", false)
              .data("attachfilter" , false)
              .attr("data-attachfilter", false)
              .find("span")
              .css({background:"none"})
              .html('');
            }
          });
          


          if (dropedElem.hasClass('oneFlt')){
             $(this).addClass("statehighlight")
            .css({background:dropedElem.css("background")})
            .html(dropedElem.data("fltid")).parent()
            .data("attachfilter", dropedElem.data("fltid"))
            .attr("data-attachfilter" , dropedElem.data("fltid"));
            $(this).data("typeField", "filter")
            .attr("data-typeField" , "filter");
          }else {
            $(this)
            .css({background:dropedElem.css("background")})
            .html(dropedElem.data("tvid")).parent()
            .data("attachtv", dropedElem.data("tvid"))
            .attr("data-attachtv" , dropedElem.data("tvid"));
             $(this).data("typeField", "tv")
            .attr("data-typeField" , "tv");
          }

          console.log($(this).parent().data("attachtv"));
          if ($(this).parent().data("attachtv") != "CONTENT" && $(this).parent().data("attachtv") != "PGT" ) {
            $(this).parent().append('<span class="addToPgt">+ PGT</span>');

          }
          $(this).parent().append('<span class="deleteThis">&times;</span>');





        }
    });    
}

  
  
  
  





function initImageDragDrop() {
  $('.imgprogress').draggable( {
      revert:"invalid",
      helper: "clone",
      zIndex: 999,
      appendTo: ".rightBL",
      start: function(event, ui) {

      },
      
      stop: function(event, ui) {
        offsetLeft = ui.position.left;
      }
      
  });
    
  $(".dropFirImages").droppable({
        accept:".imgprogress",
        hoverClass:"statehighlight",
        drop:function(event, ui){
          dropedElem = ui.draggable;
          
            
          $(this).empty()
          .append('<img src="'+dropedElem.data("linkimage")+'">')
          .data("attachimage", dropedElem.data("linkimage"))
          .attr("data-attachimage" , dropedElem.data("linkimage"));
        }
    });    
}


 






$( initDropZone ); 

function initDropZone() {
  var dropZone = $('#dropZone'),
  maxFileSize = 3000000; //  ~3 мб.
  var files;
  
  if (typeof(window.FileReader) == 'undefined') {
      dropZone.text('Не поддерживается браузером!');
      dropZone.addClass('error');
  }   
  
  dropZone[0].ondragover = function() {
    dropZone.addClass('hover');
    return false;
  };
      
  dropZone[0].ondragleave = function() {
    dropZone.removeClass('hover');
    return false;
  }; 
  
  dropZone[0].ondrop = function(event) {
    event.preventDefault();
    dropZone.removeClass('hover');
    dropZone.addClass('drop');
    
    $(".textInDrop").remove();
    
    files = event.dataTransfer.files;
    var data = new FormData();

    for (var i = 0, f; f = files[i]; i++) {
      if (!f.type.match('image.*')) {
        continue;
      }
      
      if (imageListName.in_array(f.name)) {
        continue;
      }

      imageListName.push(f.name);
      imageListState.push("wait");
      imageList.push(f);
      
      
      var reader = new FileReader();
      reader.onload = (function(theFile) {
        return function(e) {
          arrnames = theFile.name.split('.'); 
          ext = arrnames.pop();
          filename = arrnames.join('.');
          filenameNotDot = arrnames.join('');
          

          /* REMODE
          var span = $('<span/>', {
              class:  'imgprogress',
              id: 'progress_'+filenameNotDot
            }); 
            */ 
            
          var span =  $('<span>', { 
            class:  'imgprogress',
            /*id: 'progress_'+filenameNotDot*/
			
          });
		  
		  span.data("idIMGcol", filenameNotDot)
              .attr("data-idIMGcol" , filenameNotDot);
           
          span.append('<div class="progress-bar-mini orange shine"><span style="width: 0%"></span></div>');
          span.append( ['<img class="thumb" src="', e.target.result,
                            '" title="', theFile.name, '"/>'].join(''));
  
        //  $('#dropZone').append(span);  //REMODE
          span.appendTo('#dropZone');  
          data.append( i , theFile );      
        };
      })(f);
      
      reader.readAsDataURL(f);
    }

    setTimeout(uploadImage, 100 , imageList , 0);
  };
        
}





  
  
  
  
function uploadImage(data  , cnt) {
  var formData = new FormData();
  formData.append( 0 , data[cnt] );

  var current = data[cnt];
  var arrnames = current.name.split('.');  
  var ext = arrnames.pop();
  var filename = arrnames.join('.'); 
  var filenameNotDot = arrnames.join(''); 
  var progress = $('.imgprogress[data-idIMGcol="'+filenameNotDot+'"]');
				// $('td.itscolimg[data-valuep=itsimg_'+filename+']')
  
  if (imageListState[cnt] == 'loaded') {
    if (cnt+1 < data.length) {
      uploadImage(data  , cnt+1);
    }
  } else {
   $.ajax({
     url: '../assets/modules/importXLS/_action_ajax.php?uploadimages',
     type: 'POST',
     data: formData,
     cache: false,
     dataType: 'json',
     processData: false,
     contentType: false,
     xhr: function(){
       var xhr = $.ajaxSettings.xhr();
       xhr.upload.addEventListener('progress', function(evt){
         if(evt.lengthComputable) {
           var percentComplete = Math.ceil(evt.loaded / evt.total * 100);
           progress.find("span").css({width: percentComplete + '%'});
         } 
       }, true);
       return xhr;
     },
     
     success: function( respond ){
         if (respond.result){
            imageListState[cnt-1] = 'loaded';
            var percentFullComplete = Math.ceil(cnt / data.length * 100);
            progress.data("linkImage" , respond.path).attr("data-linkImage" , respond.path);
			progress.data("realName" , respond.realname).attr("data-realName" , respond.realname);
            $("#progressFillImg").css({width: percentFullComplete + '%'});
            if (percentFullComplete >= 100) {
               $("#progressFillImg").css({width: '0%'});
            }
            setTimeout(initImageDragDrop, 10);
            progress.find("span").css({display: 'none'});

            $('td.itscolimg[data-valuep="itsimg_'+filename+'"]').parent().find(".dropFirImages").addClass("statehighlight")
              .append('<img src="'+respond.path+'" />')
              .data("attachimage", respond.path)
              .attr("data-attachimage" , respond.path);
     
             if (cnt < data.length) uploadImage(data  , cnt);
         }
     }
   });  
  }   
  cnt++;  
}



$(".tabs > div").click(function(){
  $(".tabs > div").removeClass("active");
  $(this).addClass("active"); 
});



$(".tabs > .enTv").click(function(){
  $(".filterLists").removeClass("active");
  $(".tvLists").addClass("active");
});

$(".tabs > .enFlt").click(function(){
  $(".tvLists").removeClass("active");
  $(".filterLists").addClass("active");
});





$('.dooRedo').dblclick(function () {
    $.ajax({ 
      url: '../assets/modules/importXLS/_action_ajax.php?event=doRedo',
      type: 'GET',
      dataType: 'json',       
      cache: false,
      success: function( respond ){ 
        if (respond.state) {
          $(".dooRedo").html("Откатить базу на шаг (Доступно: "+respond.countStep+")");
          alert("Восcтановлено");
        }
      }
  }); 

});

















$('.processImport').click(function () {
  $("form").click(function(e){e.stopPropagation()});
});



$('form').click(function (e) {
  e.stopPropagation();
});



$('.processImport').click(function () {
  var tvCollation = Array();
  var filterCollation = Array();
 var imageCollation = Array(); 
 var imageCollationLink = Array();
  //var imageCollation = {};
  var stringsCollation = Array();
  var stringsCollationIgnore = Array();
  var imageTVcol = false;
  
  var startProcess = false;
  

/*
  alert(arrayCats+"- arrayCats");
   alert(arrayCats.length+"- arrayCatslength"); 
   alert(tocat+"- tocat");
  
  */
  if ($(this).hasClass('disabled')) {
    alert('Завершите выбор столбцов с категориями'); 
    return false;
  }
  

  
  vendorID = false;
   
  /*if ($('.selVendorList option:selected').val() == '-1') {
    alert('Выберите поставщика'); 
   return false;
  }else {
    vendorID = $('.selVendorList option:selected').val();
  }
*/





  $.each( $(".imgprogress") ,function(innerIndex, innerValue){
   
   /* imageCollation[innerIndex] = {
      realname: $(innerValue).data('realname'),
      linkimage: $(innerValue).data('linkimage'),
    };*/
    imageCollation[innerIndex]  = $(innerValue).data('realname');
    imageCollationLink[innerIndex]  = $(innerValue).data('linkimage');
  });






  
  $.each( $(".dropableCtrl") ,function(innerIndex, innerValue){
    tvCollation[$(this).data("idcol")] = $(this).data("attachtv");
    if ($(this).data("attachtv") == "PGT") {
      startProcess = true;
    }
  });


//alert(callationIndex);
  if (callationIndex !== false) {
    startProcess = true; 
  } else{
    alert("Не указан столбец с уникальными значениями");
    return;
  }




  $.each( $(".dropableCtrl") ,function(innerIndex, innerValue){
    filterCollation[$(this).data("idcol")] = $(this).data("attachfilter");    
  });


  if (!startProcess) {
    alert("Не указан столбец PAGETITLE");
    return;
  } 

   
/*
   if (arrayCats.length < 1) {
   // alert("<1 tr");
  }else {
	 //  alert("<1 f");
  }
  */


/*
  if (tocat===false && arrayCats.length < 1) {
    alert("Не выбрана категория для загрузки");
    return;
  }
  
*/
 if (tocat===false) {
    alert("Не выбрана категория для загрузки");
    return;
  }

 
  $dirtyCheck = false;
  $dirtyCheckIMGcollation = false;
  $.each( $(".checkString:checked") ,function(innerIndex, innerValue){
    stringsCollation[innerIndex] = parseInt($(this).data("stringid")) + 1;
    $dirtyCheck = true;
    $dirtyCheckIMGcollation = true;
    //imageCollation[stringsCollation[innerIndex] -1] = $(this).parent().parent().find(".dropFirImages").data("attachimage");
    /*
    imageCollation[innerIndex] = {
      id :  stringsCollation[innerIndex] -1 ,
      path : $(this).parent().parent().find(".dropFirImages").data("attachimage")
    }
    */
  });  


  $.each( $(".checkStringIgnore:checked") ,function(innerIndex, innerValue){
    stringsCollationIgnore[innerIndex] = parseInt($(this).data("stringidignore")) + 1;
    $dirtyCheck = true;

  });  
  

/*
    if (!$dirtyCheckIMGcollation){
      $.each( $(".dropFirImages") ,function(innerIndex, innerValue){
      //  imageCollation[$(this).data("rownum")] = $(this).data("attachimage");
      //stringsCollation[innerIndex] = parseInt($(this).data("stringid")) + 1;
       imageCollation[innerIndex] = $(this).data("attachimage");
       // imageCollation[innerIndex] = {
        //  id :  $(this).data("rownum"),
        //  path : $(this).data("attachimage")
       // }
      });
    }
*/
  
  
  //imageCollation  = imageCollation.serialize()




  imageTVcol = $(".idtvimg").data("attachtv");
  postv = $("#postav").val();
  
  tvCollation = JSON.stringify(tvCollation);
  filterCollation = JSON.stringify(filterCollation);
  imageCollation = JSON.stringify(imageCollation);
  imageCollationLink = JSON.stringify(imageCollationLink);
  stringsCollation = JSON.stringify(stringsCollation);
  stringsCollationIgnore = JSON.stringify(stringsCollationIgnore);
  addToPGTjson = JSON.stringify(addToPGT);
  arrayCatsJSON = JSON.stringify(arrayCats);
  arrayImgLinksJSON = JSON.stringify(arrayImgLinks);
  arrayImgLinksLocalJSON = JSON.stringify(arrayImgLinksLocal);

   $(".infoArea").fadeIn()
   $(".infoArea span").html("Обработано позиций: 0").removeClass("finished");
   $(".infoArea img").fadeIn();



  // console.log(arrayImgLinksJSON);
  
    $.ajax({ 
      url: '../assets/modules/importXLS/_action_ajax.php?event=doBackUp',
      //url: '../assets/modules/importXLS/_action_ajax.php?event=dooImportData',
      type: 'POST',
      dataType: 'json',       
      cache: false,
      success: function( respond ){ 
        if (respond.state) {
          console.log(
            "pathXLS " + pathXLS + " - " +
            "tocat " + tocat + " - " +
            "imageCollation " + imageCollation + " - " +
            "tvCollation " + tvCollation + " - " +
            "imageTVcol " + imageTVcol + " - " +
            "typeImport " + typeImport + " - " +
            "currentSheet " + currentSheet + " - " +
            "stringsCollation " + stringsCollation + " - " +
            "callationIndex " + callationIndex + " - " +
            "arrayCatsJSON " + arrayCatsJSON + " - " +
            "startFrom 0 - " +
            "postv " + postv + " - " +
            "arrayImgLinksJSON " + arrayImgLinksJSON + " - " +
            "filterCollation " + filterCollation + " - " +
            "arrayImgLinksLocalJSON " + arrayImgLinksLocalJSON + " - " +
            "stringsCollationIgnore " + stringsCollationIgnore + " - " +
            "vendorID " + vendorID + " - "
          );

          iterateRecursiveImportBigBigDataMegaFunction(
            pathXLS,
            tocat,
            imageCollation,
            imageCollationLink,
            tvCollation,
            imageTVcol,
            typeImport,
            currentSheet,
            stringsCollation,
            callationIndex,
            arrayCatsJSON,
            0,
            postv,
            arrayImgLinksJSON,
            filterCollation,
            arrayImgLinksLocalJSON,
            stringsCollationIgnore, 
            vendorID
            );
        }
      }
  }); 



//iterateRecursiveImportBigBigDataMegaFunction(pathXLS,tocat,imageCollation,tvCollation,imageTVcol,typeImport,currentSheet,stringsCollation,callationIndex,arrayCatsJSON,0,postv,arrayImgLinksJSON , filterCollation);

  
  /* 
  $.ajax({
      url: '../assets/modules/importXLS/_action_ajax.php?dooImportData',
      type: 'POST',
      dataType: 'json',
      data: {
        pathToXLS: pathXLS,
        tocat:tocat,
        imageCollation:imageCollation,
        tvCollation:tvCollation,
        imageTVcol:imageTVcol,
        typeImport:typeImport,
        currentSheet:currentSheet,
        stringsCollation:stringsCollation,
        callationIndex:callationIndex, //сравнение по столбцу 
        selectColCat1st_pos:arrayCatsJSON
      },
      
      cache: false,
      success: function( respond ){ 
          console.log(respond);
          
        //$(".darkBG").css({display:'block'});
        //$(".modalNotice").css({display:'block'});
        $("#noticeUpl_add").html(respond.meta.added);
        $("#noticeUpl_upd").html(respond.meta.updated);
        $("#noticeUpl_cpath").html(respond.meta.createNewPath);
      }
  });  
*/


});
  

function iterateRecursiveImportBigBigDataMegaFunction(pathXLS,tocat,imageCollation,imageCollationLink,tvCollation,imageTVcol,typeImport,currentSheet,stringsCollation,callationIndex,arrayCatsJSON,startFrom,postv , arrayImgLinksJSON=false , filterCollation, arrayImgLinksLocalJSON=false , stringsCollationIgnore = false , vendorID = false) {
  
 // if (vendorID == false ) return ;
  $.ajax({
      url: '../assets/modules/importXLS/_action_ajax.php?dooImportData',
      type: 'POST',
      dataType: 'json',
      data: {
        pathToXLS: pathXLS,
        tocat:tocat,
        addToPGT:addToPGTjson,
        imageCollation:imageCollation,
        imageCollationLink:imageCollationLink,
        collationImageCol:collationImageCol,
        tvCollation:tvCollation,
        filterCollation:filterCollation,
        imageTVcol:imageTVcol,
        typeImport:typeImport,
        currentSheet:currentSheet,
        stringsCollation:stringsCollation,
        stringsCollationIgnore:stringsCollationIgnore,
        callationIndex:callationIndex, //сравнение по столбцу 
        selectColCat1st_pos:arrayCatsJSON, 
        selectAcolLonkImg_pos:arrayImgLinksJSON, 
        selectAcolLocalImg_pos:arrayImgLinksLocalJSON, 
        startFrom:startFrom,
        postv:postv, // special for prolegion
        vendorID:vendorID // special for prolegion
      },
      
      cache: false,
      success: function( respond ){
         //console.log(respond.highestRow);
          if (respond.finished != true) {
          	if (respond.highestRow){
 				iterateRecursiveImportBigBigDataMegaFunction(pathXLS,tocat,imageCollation,imageCollationLink,tvCollation,imageTVcol,typeImport,currentSheet,stringsCollation,callationIndex,arrayCatsJSON,respond.highestRow,postv , arrayImgLinksJSON,filterCollation, arrayImgLinksLocalJSON , stringsCollationIgnore ,vendorID)
          	    
          	  //  $(".darkBG").css({display:'block'});
		        //$(".modalNotice").css({display:'block'});
		        //$("#noticeUpl_processed").html(respond.highestRow); 
		       // $("#noticeUpl_add").html(respond.highestRow); 
            $(".infoArea span").html("Обработано позиций: "+respond.highestRow);

		       /* $("#noticeUpl_add").html( parseInt($("#noticeUpl_add").text()) +  respond.meta.added);
		        $("#noticeUpl_upd").html(parseInt($("#noticeUpl_upd").text()) + respond.meta.updated);
		        $("#noticeUpl_cpath").html(parseInt($("#noticeUpl_cpath").text()) + respond.meta.createNewPath);
*/
          	}else {
          		


              $(".dopBtn.delNotUpdated").addClass("active");
              $(".infoArea span").html("Импорт завершен").addClass("finished");
              $(".infoArea img").fadeOut();
          	}

           
          }else {
              $(".dopBtn.delNotUpdated").addClass("active");
              $(".infoArea span").html("Импорт завершен");
              $(".infoArea img").fadeOut();
          }

          

      }
  });  
}

  










function setFixedRow() {
  //мегачерезжопнаяфункция!!
  var div = $('#fixedROW');
  var div2 = $('#fixedROW2');
  var start = $(div).offset().top;

  var start2 = $(div2).offset().top;
  var fnPattern = /width:\s*(\d*)px/i;  

  $.each( div.find('td') ,function(innerIndex, innerValue){
    wpxt = ($(this).width());
    $(this).css({width:(parseInt(wpxt)+1)+"px"});
    
    $(this).attr("width" , (parseInt(wpxt)+1) );
  });
  
  $.each( div2.find('td') ,function(innerIndex, innerValue){
    wpxt = (div.find('td')[innerIndex]);
    found = wpxt.width;

    $(this).css({width:(parseInt(found))+"px"});
  });
  
  $('#fixedROW td:last').css({
    width: ($('.etlWidthed:first td:last').width()-14)+'px'
  });
  
  $('#fixedROW2 td:last').css({
    width: ($('.etlWidthed:first td:last').width()-14)+'px'
  });
  
  div.css({position:'fixed'});
  div2.css({position:'fixed'});
  
  $(".prepaireTable table").css({marginTop:'69px'});


  $(document).on('scroll', function(){
    var scrlTWindow = $(this).scrollTop();
    /*
    div.css({top:(start-scrlTWindow+56)+'px'});
    div2.css({top:(start2-scrlTWindow+57)+'px'});
    */
    div.css({top:(start-scrlTWindow)+'px'});
    div2.css({top:(start2-scrlTWindow)+'px'});
  });
  $(document).scroll(); 
}

































































function refreshDetectImages() {

	/*
  $(".dropFirImages").removeClass("statehighlight");
  $(".dropFirImages").empty(); 
  $(".dropFirImages").data("attachimage" , false);
  $(".dropFirImages").removeData();
  $(".dropFirImages").removeAttr('data-attachimage');
  */
  $.each( $(".imgprogress") ,function(innerIndex, innerValue){

    vals = $(innerValue);
   
/*   
    arrnames = vals.data("linkimage").split('/');
    lastpath = arrnames.pop();
    arrnames = lastpath.split('.');
	*/
	
	//arrnames = vals.data("linkimage").split('.');

    filename = vals.data("realname").split('.')[0];
  
    linkimage = vals.data("linkimage")
    
	//alert(filename);
	// console.log(filename);
	// console.log($('td.itscolimg[data-valuep="itsimg_'+filename+'"]'));

  console.log($('td.itscolimg[data-valuep="itsimg_'+filename+'"]'));
    
    $('td.itscolimg[data-valuep="itsimg_'+filename+'"]').parent().find(".dropFirImages").addClass("statehighlight")
	  .empty()
      .append('<img src="'+linkimage+'" />')
      .data("attachimage", linkimage)
      .attr("data-attachimage" , linkimage);
      console.log(linkimage);
    
  });

}










$("body").on('click','.checkString',function(event){
  event.stopPropagation();
});





 

$("body").on('click','.xlsTable tr td:not(.dropableCtrl)',function(event){
  var cell = event.target;

  if (cell.cellIndex < 4 || (!selectAimg  && !selectAunique && !selectColCat1st && !selectAcolLonkImg && !selectAcolLocalImg)) return;
  //if (cell.cellIndex > 2 && !selectAimg) return false;
  //var elems = $("tr td:eq("+cell.cellIndex+")");
  ///cursElm = elems.get(cell.cellIndex);
  if (selectAimg) {
    selectAimg = false;
    $('.selectAcolsImg .miniAddsTx').html("Выбран столбец " + $("tr:eq(1) td:eq("+cell.cellIndex+")").text()  );
    //itscolimg itsimg_116460
    $('td').removeClass('itscolimg');
     tds = $( this ).parent().find("td"),
     index = $.inArray( this, tds ),
     sel_tds = $("td:nth-child("+( index + 1 )+")");
     sel_tds.addClass('itscolimg');
     //console.log('fffdfdfd');

     collationImageCol = index - 4;

     refreshDetectImages();
  }
  
  if (selectAunique) {
    selectAunique = false; 
    if  ( typeof  $("tr:eq(0) td:eq("+cell.cellIndex+")").data('attachtv') == "undefined") { 
	
		// console.log($("tr:eq(0) td:eq("+cell.cellIndex+")"));
		// console.log($("tr:eq(0) td:eq("+cell.cellIndex+")").data('attachtv'));
      alert ("Этому столбцу не назначен TV"); 
      $('.selectAunique .miniAddsTx').html("Выбрать столбец" );
    }else {
      callationIndex = cell.cellIndex-4;  
      $('.selectAunique .miniAddsTx').html("Выбран столбец " + $("tr:eq(1) td:eq("+cell.cellIndex+")").text()  );
    }
  }


  
 


  if (selectColCat1st) {
   // selectColCat1st = false; 
    selectColCat1st_pos = cell.cellIndex-4;  
	
	arrayCats.push(selectColCat1st_pos);
	arrayCols.push($("tr:eq(1) td:eq("+cell.cellIndex+")").text());
	
    //$("#px_f_1").prop("checked",  false )
    $("#px_f_2").prop("checked",  true )
	
	buf = '';
	firstIter = true;
	$.each(arrayCols , function(key, value){
		if (firstIter){
			buf += value;
			firstIter = false;
		}else {
			buf += '->'+value;
		}
		
		
	});

    $('.selectAcolCat1st .miniAddsTx').html(buf);
    $('.px_tesxCol').html('Готово');
    
	// console.log(selectColCat1st);
	// console.log(arrayCats);
  }








 if (selectAcolLonkImg) {
   // selectColCat1st = false; 
    selectAcolLonkImg_pos = cell.cellIndex-4;  
  
  arrayImgLinks.push(selectAcolLonkImg_pos);
  arrayImgLinksCols.push($("tr:eq(1) td:eq("+cell.cellIndex+")").text());
  
    //$("#px_f_1").prop("checked",  false )
    $("#px_f_3").prop("checked",  true )
  
  buf = '';
  firstIter = true;
  $.each(arrayImgLinksCols , function(key, value){
    if (firstIter){
      buf += value;
      firstIter = false;
    }else {
      buf += '->'+value;
    }
    
    
  });

    $('.selectAcolLonkImg .miniAddsTx').html(buf);
    $('.px_tesxCol').html('Готово');
    
  // console.log(selectAcolLonkImg);
  // console.log(arrayImgLinks);
  }




 if (selectAcolLocalImg) {
   // selectColCat1st = false; 
    selectAcolLocalImg_pos = cell.cellIndex-4;  
  
  arrayImgLinksLocal.push(selectAcolLocalImg_pos);
  arrayImgLinksColsLocal.push($("tr:eq(1) td:eq("+cell.cellIndex+")").text());
  
    //$("#px_f_1").prop("checked",  false )
    $("#px_f_4").prop("checked",  true )
  
  buf = '';
  firstIter = true;
  $.each(arrayImgLinksColsLocal , function(key, value){
    if (firstIter){
      buf += value;
      firstIter = false;
    }else {
      buf += '->'+value;
    }
    
    
  });

    $('.selectAcolLocalImg .miniAddsTx').html(buf);
    $('.px_tesxCol').html('Готово');
    
  // console.log(selectAcolLonkImg);
  // console.log(arrayImgLinks);
  }







});



$("body").on('click','.selectAcolsImg',function(event){
  selectAimg = true;
  $(this).find('.miniAddsTx').html("Нажмите на нужный столбец");
});



$("body").on('click','.selectAunique',function(event){
  selectAunique = true;
  $(this).find('.miniAddsTx').html("Нажмите на нужный столбец");
});



$("body").on('click','.selectAcolCat1st',function(event){
  if (selectColCat1st) {
    if (arrayCats.length<1) return false;
    selectColCat1st = false;    
    typeImport = "toChangedCat";
    $('.px_tesxCol').html('Столбец с категориями');
    $(this).removeClass("redCode");
    $('.processImport').removeClass("disabled");
    //alert (typeImport); 
    // console.log(selectColCat1st);
    // console.log(arrayCats);
  
  }else {
    selectColCat1st = true;
    $(this).find('.miniAddsTx').html("Нажмите на нужный столбец");
     $(this).addClass("redCode");
     $('.processImport').addClass("disabled");
    arrayCats = Array();
    arrayCols = Array();
  
  
  }
  

});


$("body").on('click','.selectAcolLonkImg',function(event){
	if (selectAcolLonkImg) {
		if (arrayImgLinks.length<1) return false;
		selectAcolLonkImg = false;    
		//typeImport = "toChangedCat";
		$('.px_tesxCol').html('Столбцы с картинками');
    $(this).removeClass("redCode");
    $('.processImport').removeClass("disabled");
		//alert (typeImport); 
		//console.log(selectAcolLonkImg);

	
	}else {
		selectAcolLonkImg = true;
		$(this).find('.miniAddsTx').html("Нажмите на нужный столбец");
     $(this).addClass("redCode");
     $('.processImport').addClass("disabled");
		arrayImgLinks = Array();
		arrayImgLinksCols = Array();
	
	
	}
	

});




$("body").on('click','.selectAcolLocalImg',function(event){
  if (selectAcolLocalImg) {
    if (arrayImgLinksLocal.length<1) return false;
    selectAcolLocalImg = false;    
    //typeImport = "toChangedCat";
    $('.px_tesxCol').html('Столбцы с картинками');
    $(this).removeClass("redCode");
    $('.processImport').removeClass("disabled");
    //alert (typeImport); 
    //console.log(selectAcolLonkImg);

  
  }else {
    selectAcolLocalImg = true;
    $(this).find('.miniAddsTx').html("Нажмите на нужный столбец");
     $(this).addClass("redCode");
     $('.processImport').addClass("disabled");
    arrayImgLinksLocal = Array();
    arrayImgLinksColsLocal = Array();
  
  
  }
  

});








$("body").on('mouseover','.xlsTable td',function(event){
    if (!selectAimg && !selectAunique && !selectColCat1st && !selectAcolLonkImg && !selectAcolLocalImg) return false;
    tds = $( this ).parent().find("td"),
    index = $.inArray( this, tds ),
    sel_tds = $("td:nth-child("+( index + 1 )+")");
    if (index < 3) return;

    sel_tds.css("background-color", "#dbf7c5");
});



$("body").on('mouseout','.xlsTable td',function(event){
  //if (!selectAimg) return false;
  tds = $( this ).parent().find("td"),
  index = $.inArray( this, tds ),
  sel_tds = $("td:nth-child("+( index + 1 )+")");
  
  sel_tds.css("background-color", "#fff");

});






}); // end of ready



