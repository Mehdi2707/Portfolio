import $ from 'jquery';

$(document).ready(function () {
    $(".generate_name").on("click", function () {
        const url = $(this).data('url');
        generate_name(url);
    });
    $(".generate_profil").on("click", function () {
        const url = $(this).data('url');
        generate_profil(url);
    });

    setTimeout(function() {
        $('#profile-image').addClass('rotated');
    }, 250);
    setTimeout(function() {
        $('#profile-image').removeClass('rotated');
    }, 1000);

    $("#btn_accueil, #btn_experiences, #btn_formations, #btn_contact").on("click", function () {
        const attrId = $(this).attr('id');
        const url = $('#'+attrId).data('url');
        $('.contenu').fadeOut(1000, function() {
            generate_html(url);
            $('.contenu').fadeIn(1000);
        });
    });

    $(".btn_contact").on("click", function () {
        const url = $(this).data('url');
        $('.contenu').fadeOut(1000, function() {
            generate_html(url);
            $('.contenu').fadeIn(1000);
        });
    });
});

const mediaQuery = window.matchMedia('(max-width: 768px)');

function handleTabletChange(e) {
    // Check if the media query is true
    if (e.matches) {
        // Ajouter la classe "mobile" à l'élément avec l'id "example"
        $('.btn-media').addClass('button-media');
    } else {
        // Supprimer la classe "mobile" de l'élément avec l'id "example"
        $('.btn-media').removeClass('button-media');
    }
}

// Ajouter un listener pour écouter les changements de la largeur de l'écran
mediaQuery.addListener(handleTabletChange);

// Appeler la fonction handleTabletChange au chargement de la page
handleTabletChange(mediaQuery);


$(window).scroll(function() {
    if ($(window).scrollTop() > 0) {
        $('.header').css('background-color', '#000C2C');
        $('.header').css('transition', 'background-color 0.5s ease-in-out');
    } else {
        $('.header').css('background-color', 'rgba(255, 255, 255, 0.2)');
        $('.header').css('transition', 'background-color 0.5s ease-in-out');
    }
});

function generate_html(url)
{
    $.get(url, function(data) {
        $('.contenu').html(data);
    });
}
function generate_profil(url)
{
    $.get(url, function(data) {
        var data = JSON.parse(data);
        var profil = $("#profil");
        var name = data.name;
        var address = data.address;
        const dateArray = data.birth_data.split("-");
        const formattedDate = dateArray[2] + "/" + dateArray[1] + "/" + dateArray[0];

        profil.html('</br>Nom : ' + name + '</br>Adresse : ' + address + '</br>Date de naissance : ' + formattedDate);
    });
}

function generate_name(url)
{
    $.get(url, function(data) {
        const lines = data.split('\n');
        const names = [];
        for (let i = 0; i < lines.length; i++) {
            names.push(lines[i].split(',')[0]);
        }
        const randomIndex = Math.floor(Math.random() * names.length);
        const capitalizedName = names[randomIndex].slice(0, 1).toUpperCase() + names[randomIndex].slice(1);
        $("#nom").html(capitalizedName);
    });
}