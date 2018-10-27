<?php
/*
  @author Miguel Alvim, Marluce Ap. Vitor
  @version 
  @date 19/08/2018
*/
//require $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once('../../config.php');
//require_once($CFG->dirroot . '/my/lib.php');
require __DIR__ . '/ZaraFunctions.php';
// requerimento de css
require_once ('styles.css');
// Global Variables
global $PAGE;
global $OUTPUT;
global $DB;
// Pre defined arrays
$arrCursos = array(
    1 => 'bavi'
);
// Local Variables
$urlCourse   = '/ovr/index.php?id=';
$courseData  = new stdClass();
$courseId    = 2;
$banUser     = '1, 2, 3, 4, 5, 6, 7, 930, 931';
$allUser   = array();
$allUserBF = array();
$accessUser   = array();
$accessUserBF = array();
$notAccessUser   = array();
$notAccessUserBF = array();
$approved   = array();
$approvedBF = array();
$repproved   = array();
$repprovedBF = array();
$arrResult = array();
// Getting data via $_GET
if (isset($_GET['id'])) {
    $courseId = $_GET['id'];
}
// Receiving course data and logging verification
$courseData = $DB->get_record("course", array("id" => $courseId), "*", MUST_EXIST);
require_login($courseData);
// Starting support classes
$fnc = new ZaraFunctions();
// Selecting data 
// All course users
$arrResult = $fnc->selectAllUsers($courseId, $banUser);
foreach ($arrResult as $result) {
    $allUserBF[$result['department']][] = $result['userid'];
    $allUser[] = $result['userid'];
}
// Users that use the course
$arrResult = $fnc->selectUserAccess($courseId, $banUser);
foreach ($arrResult as $result) {
    $accessUserBF[$result['department']][] = $result['userid'];
    $accessUser[] = $result['userid'];
}
// Users that do not access the course
$arrResult = $fnc->selectUserNotAccess($courseId, $banUser);
foreach ($arrResult as $result) {
    $notAccessUserBF[$result['department']][] = $result['userid'];
    $notAccessUser[] = $result['userid'];
}

?>

<?php
$courseContext = context_course::instance($courseData->id);
$PAGE->set_title('ovr');
$PAGE->set_heading($courseData->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading('videoaulas');
?>
  <!--scripts for the search and submit buttons-->
  <script type="text/javascript">
     var searchText = "";
      function bttBusca(){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              var json = JSON.parse(this.responseText);
              var aux =""
              for(var i=0;i <json.length; ++i){                
                var title = json[i].substring(json[i].lastIndexOf('/')+1,(json[i].length-4));
                aux = aux+
                "<div class='col-md-4'><div class='col-md-4'><input type='checkbox' name='your-group' id='combo"+i+"'/>"+
                "<br></div><video class='center' width='85%' height='25%' controls id='comboVideo"+i+"' src="+json[i]+">"+"</video><div class='col-md-4.legenda' id='legenda' align='center'><textarea class='legenda' id='editLegenda"+i+"'>"+title+"</textarea></div></br></br></div>";
              }
              document.getElementById('videos').innerHTML = aux+"<div class='col-md-12'><button class='btn1 btn-primary' type='submit' onclick='bttSubmit()'>Enviar</button></div>";
           } 
        };
        searchText = document.getElementById('textBusca').value;
        xhttp.open("GET", "ajaxreceiver.php?keyword="+searchText, true);
        xhttp.send();
      }
    function bttSubmit(){
      var videos = {};
      var names = {};
      var totVideos =0;
      for (var i =0; ;++i){//Getting the videos url
        var box = document.getElementById('combo'+i);
        if (box != null){
          if (box.checked ){
            videos[totVideos]=(document.getElementById('comboVideo'+i).src);
            names[totVideos]=(document.getElementById('editLegenda'+i).value);
            //alert(names[totVideos]);
            ++totVideos;
          }
        }else{
          break;
        }
      }
      var videosJson = JSON.stringify(videos);
      var xhttp = new XMLHttpRequest();//sending AJAX request with the urls for database insertion
      xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
/*            if(this.responseText == 'true'){
              alert("URLs criadas com sucesso "+this.responseText);
            }else{
              alert("Erro ao criar 1 ou mais URLs");
            }*/
          //  alert(this.responseText);
              //Regex used to read the current url
              var expression = /^.*\/(?=blocks)/;
              var currentURL = (window.location.href); 
              var courseURL = expression.exec(currentURL); 
              if(courseURL){
                var getParams = getGETParams();
                courseURL = courseURL+"course/view.php?id="+getParams.id;
                window.location.replace(courseURL);
              }
         }
      };
      //POST parameters
      var getParams = getGETParams();
      var namesJson = JSON.stringify(names);
      var rotName = document.getElementById('textBusca').value;
       xhttp.open("POST","ajaxsubmit.php", true);
      xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhttp.send("cid="+getParams.id+"&urls="+videosJson+"&names="+namesJson+"&searchText="+searchText
            +"&section="+getParams.section+"&rotName="+rotName);
    }
    //Putting the values received from GET in an array
    function getGETParams(){
      var vals = window.location.search.substr(1);
      if(vals == null || vals == ""){
        return {};
      }else
        return getToArray(vals);
    }
    function getToArray(vals){
      var params = {};
      var nval = vals.split("&");
      for ( var i = 0; i < nval.length; ++i) {
        var tempVals = nval[i].split("=");
        params[tempVals[0]] = tempVals[1];
      }
      return params;
    }
    function enter(evento){ 
           tecla = evento.keyCode;
           if(tecla == 0)
           {
                   tecla = evento.charCode;
           }
           if(tecla == 13)
           {
            bttBusca();
           }
    }
  </script>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">

    <div class="plugin" align="center">
            <br> <br>
      <h2><b><font color="##1E90FF"> Pesquisa: </b></h2><br> </font> 
      <input class="ls-field-sm" id="textBusca" size="80" name="pesquisa" title="Pesquisar" type="search" onkeypress="enter(event);" >
      <br> <br> 
      <button class="btn btn-primary"  type="button" onclick="bttBusca()"> Buscar</button> 
      <br> <br>
    </div>
  
   
  <div id="videos">
  </div>



<?= $OUTPUT->footer(); ?>
