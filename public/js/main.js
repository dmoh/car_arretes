!function(a){a.fn.datepicker.dates.fr={days:["dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi"],daysShort:["dim.","lun.","mar.","mer.","jeu.","ven.","sam."],daysMin:["d","l","ma","me","j","v","s"],months:["janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre"],monthsShort:["janv.","févr.","mars","avril","mai","juin","juil.","août","sept.","oct.","nov.","déc."],today:"Aujourd'hui",monthsTitle:"Mois",clear:"Effacer",weekStart:1,format:"dd/mm/yyyy"}}(jQuery);
$(document).ready(function(){
    $('.js-datepicker').datepicker({
       format: 'yyyy-mm-dd',
       language: 'fr'
    });

    //Validation Côté client

    var $car_immat = $('car_immat');

    var $r = $("td:contains('roulant')");
    var $ra = $("td:contains('roulant ano')");
    var $pa = $("tr > td:contains('panne')");
    $r.css("color", "green");
    $ra.css("color", "orange");
    $pa.css("color", "red");



});