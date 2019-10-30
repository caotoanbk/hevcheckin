/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import moment from 'moment';
import VueRouter from 'vue-router'
import { Form, HasError, AlertError } from 'vform'

// ES6 Modules or TypeScript
import swal from 'sweetalert2'
window.swal = swal

const toast = swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
})

window.toast = toast


window.Form = Form
Vue.component(HasError.name, HasError)
Vue.component(AlertError.name, AlertError)
Vue.component('pagination', require('laravel-vue-pagination'));

Vue.use(VueRouter)
import VueProgressBar from 'vue-progressbar'
Vue.use(VueProgressBar, {
  color: 'rgb(143, 255, 199)',
  failedColor: 'red',
  height: '3px'
})

import DashboardComponent from './components/DashboardComponent.vue';
import CardComponent from './components/CardComponent.vue';
import UserComponent from './components/UserComponent.vue';
import EmployeeComponent from './components/EmployeeComponent.vue';
import HistoryComponent from './components/HistoryComponent.vue';
import SupplierComponent from './components/SupplierComponent.vue';
// import DeveloperComponent from './components/DeveloperComponent.vue';

let routes = [
  { path: '/dashboard', component: DashboardComponent },
  { path: '/user', component: UserComponent },
  { path: '/history', component: HistoryComponent },
  { path: '/card', component: CardComponent, props: (route) => ({ type: route.query.type }) },
  { path: '/employee', component: EmployeeComponent, props: (route) => ({ type: route.query.type }) },
  { path: '/supplier', component: SupplierComponent}
]

const router = new VueRouter({
    mode: 'history',
  routes // short for `routes: routes`
})

Vue.filter('upText', function(text){
    return text.charAt(0).toUpperCase() + text.slice(1);
});

Vue.filter('myDate', function(created) {
    return moment(created).format('MMM Do YYYY');
});

window.Fire = new Vue();

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    data: {
        search: '',
        currentUser: {}
    },
    methods:{
        searchit: _.debounce(() => {
            Fire.$emit('searching');
        }, 600)
    },
    created() {
        axios.get('current-user').then ((response) => {
            this.currentUser = response.data;
        });
    },
});
