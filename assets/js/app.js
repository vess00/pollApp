/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
var $ = require('jquery');

//We need bootstrap
require('bootstrap');

//handle js routing
let Routing = require ('../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router');
let Routes = require('./js_routing');

Routing.setRoutingData(Routes);

import Vue from 'vue';
import Vote from './components/Vote';

/**
 * Create a fresh Vue Application instance
 */
new Vue({
    el: 'vote',
    components: {Vote}
});



$(".btn-vote").click(function() {

    let record_id = $(this).data('id');

    new Promise(function (resolve,reject) {

        let url = Routing.generate('vote', {id: record_id});
        let xhr = new XMLHttpRequest();

        xhr.open("GET",url);

        xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');

        xhr.addEventListener('load', function(event){
           if (this.readyState === 4) {
               if (this.status === 200 && this.statusText === "OK") {
                   resolve (JSON.parse(this.responseText));
               }
               else {
                   reject(JSON.parse(this.responseText))
               }

           }
        });

        xhr.send();

    })
        .then((response) => {
            //show love
            //console.log("#votes-"+record_id+"   -> OK");
            $("#votes-"+record_id).text(response.votes);
            //console.log(eventSource);
        })
        .catch((error) => {
            //console.log("#votes-"+record_id+"   -> ERRRR");
            $("#votes-"+record_id).text("ERRR!");
        });

});

// The subscriber subscribes to updates for the https://example.com/foo topic
// and to any topic matching https://example.com/books/{name}

const url = new URL('http://localhost:3000/hub');
url.searchParams.append('topic', 'http://localhost:8000/api/polls/{id}');

const eventSource = new EventSource(url);

console.log(eventSource);

eventSource.onopen = e => console.log("CHANEL OPEN: " + e);
eventSource.onerror = e => console.log("CHANEL ERROR: " + e);

// The callback will be called every time an update is published
eventSource.onmessage = (e) => {
    const vote = JSON.parse(e.data);
    const id = vote["id"];
    const votes = vote["votes"];
    console.log("id: " + id + " | votes: " + votes);

    //update the UI ... fingers crossed
    $("#votes-"+id).text(votes);
}

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
