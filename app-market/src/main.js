import 'devextreme/dist/css/dx.common.css';
import 'devextreme/dist/css/dx.light.css';
import {createApp} from 'vue'
import {createPinia} from 'pinia'
import VueAxios from 'vue-axios';
import axios from 'axios';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import {library} from "@fortawesome/fontawesome-svg-core";
import {fas} from "@fortawesome/free-solid-svg-icons";
import {fab} from '@fortawesome/free-brands-svg-icons';
import {far} from '@fortawesome/free-regular-svg-icons';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
library.add(fas, fab, far);

import App from './App.vue'
import router from './router'
import './assets/scss/style.scss'
import 'devextreme/dist/css/dx.light.css';
import AOS from 'aos';
import 'aos/dist/aos.css'
import CoverPerusahaan from './components/pages-admin/CoverPerushaan.vue';

const app = createApp(App)

app.AOS = new AOS.init({
    offset: 120,
    duration: 3000,
});

// const token = '7|fP1rO2mhbMsHIanSSam51pROsgzDT83evLq56omm'
const token = localStorage.getItem('key_user');
axios.defaults.headers.common = {
    Authorization: `Bearer ${token}`
}
axios.defaults.baseURL = 'https://api-sosis.becatbeleq.com/api/v1'
// axios.defaults.baseURL = ' http://192.168.0.105:81/api/v1'

// or, using the defaults with no stylesheet
app.component("fa-icon", FontAwesomeIcon)
app.component("cover-perusahaan", CoverPerusahaan)
app.use(VueSweetalert2);
app.use(VueAxios, axios)
app.use(createPinia())
app.use(router)

app.mount('#app')
