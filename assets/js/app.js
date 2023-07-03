import $ from 'jquery';

$(document).ready(function () {
    $(document).on("click", ".generate_name", function () {
        const url = $(this).data('url');
        generate_name(url);
    });
    $(document).on("click", ".generate_profil", function () {
        const url = $(this).data('url');
        generate_profil(url);
    });

    setTimeout(function() {
        $('#profile-image').addClass('rotated');
    }, 250);
    setTimeout(function() {
        $('#profile-image').removeClass('rotated');
    }, 1000);

    $("#btn_accueil, #btn_experiences, #btn_formations, #btn_contact, #generateur_profil, #generateur_nom").on("click", function () {
        const attrId = $(this).attr('id');
        const url = $('#'+attrId).data('url');
        $('.contenu').fadeOut(500, function() {
            generate_html(url);
            $('.contenu').fadeIn(500);
        });
    });

    $(document).on("click", ".btn_contact", function () {
        const url = $(this).data('url');
        $('.contenu').fadeOut(500, function() {
            generate_html(url);
            $('.contenu').fadeIn(500);
        });
    });

    $('.navbar-toggler').on('click', function () {
        $('.contenu').toggleClass('add-margin-media');
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

        profil.html('Nom : ' + name + '</br>Adresse : ' + address + '</br>Date de naissance : ' + formattedDate);
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

/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';
import { Dropdown } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => { new App(); });

class App
{
    constructor()
    {
        this.enableDropdowns();
        this.handleCommentForm();
    }

    enableDropdowns()
    {
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle')
        const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new Dropdown(dropdownToggleEl))
    }

    handleCommentForm()
    {
        const commentForm = document.querySelector('form.comment-form');

        if(null === commentForm)
            return;

        commentForm.addEventListener('submit', async(e) => {
            e.preventDefault();

            const response = await fetch('/ajax/comments', {
                method: 'POST',
                body: new FormData(e.target)
            });

            if(!response.ok)
                return;

            const json = await response.json();

            if(json.code === 'COMMENT_ADDED_SUCCESSFULLY')
            {
                const commentList = document.querySelector('.comment-list');
                const commentCount = document.querySelector('.comment-count');
                const commentContent = document.querySelector('#comment_content');
                commentList.insertAdjacentHTML('afterbegin', json.message);
                commentCount.innerText = json.numberOfComments;
                commentContent.value = '';
            }
        })
    }
}