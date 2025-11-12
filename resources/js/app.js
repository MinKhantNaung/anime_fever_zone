import './bootstrap';

import intersect from '@alpinejs/intersect'
Alpine.plugin(intersect)

import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
// import Swiper and modules styles
import 'swiper/css';
import 'swiper/css/pagination';

import Swal from 'sweetalert2'

// Video.js and videojs-youtube
import videojs from 'video.js';
import 'video.js/dist/video-js.css';
import 'videojs-youtube';

window.Swiper = Swiper;
window.Navigation = Navigation;
window.Pagination = Pagination;

window.Swal = Swal;
window.videojs = videojs;

