import {defineStore} from "pinia";
import axios from 'axios';
import {inject} from 'vue';
import moment from 'moment';

export const useBoedysoft = defineStore('boedysoft', {
    state: () => ({
        count: 25,
        axios,
        data: {},
    }),
    getters: {
        getinfo: (state) => (kali) => state.count * kali,
    },
    actions: {
        async getData(url) {
            const res = await axios.get(url);
            this.data = res.data;
        },
        getNama() {
            return 'I Wayan Budiartha'
        },
        groupBy(array, key) {
            const result = {}
            array.forEach(item => {
                if (!result[item[key]]) {
                    result[item[key]] = []
                }
                result[item[key]].push(item)
            })
            return result
        },
        debounce(fn, delay) {
            var timeoutID = null
            return function () {
                clearTimeout(timeoutID)
                var args = arguments
                var that = this
                timeoutID = setTimeout(function () {
                    fn.apply(that, args)
                }, delay)
            }
        },
        NumberFormat(nil, desimal = 1) {
            return Intl.NumberFormat().format(nil, desimal);
        },
        DateFormat(tgl, typeFormat = 'YYYY-MM-DD') {
            return moment(tgl).format(typeFormat);
        },
        LastDate(tgl, typeFormat = 'YYYY-MM-DD') {
            return moment(tgl).endOf('month').format(typeFormat);
        },
        FirstDate(tgl, typeFormat = 'YYYY-MM-DD') {
            return moment(tgl).startOf('month').format(typeFormat);
        },
        printPeper(keyId) {
            const prtHtml = document.getElementById(keyId).innerHTML;

// Get all stylesheets HTML
            let stylesHtml = '';
            for (const node of [...document.querySelectorAll('link[rel="stylesheet"], style')]) {
                stylesHtml += node.outerHTML;
            }

// Open the print window
            const WinPrint = window.open('', '', 'left=0,top=0,toolbar=0,scrollbars=0,status=0');

            WinPrint.document.write(`<!DOCTYPE html>
                                <html>
                                  <head>
                                    ${stylesHtml}
                                  </head>
                                  <body>
                                    ${prtHtml}
                                  </body>
                                </html>`);

            WinPrint.document.close();
            WinPrint.focus();
            setTimeout(() => {
                WinPrint.print();
                WinPrint.close();
            }, 1000)

        },

        terbilang(angka) {
                var bilne = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

                if (angka < 12) {

                    return bilne[angka];

                } else if (angka < 20) {

                    return this.terbilang(angka - 10) + " belas";

                } else if (angka < 100) {

                    return this.terbilang(Math.floor(parseInt(angka) / 10)) + " puluh " + this.terbilang(parseInt(angka) % 10);

                } else if (angka < 200) {

                    return "seratus " + this.terbilang(parseInt(angka) - 100);

                } else if (angka < 1000) {

                    return this.terbilang(Math.floor(parseInt(angka) / 100)) + " ratus " + this.terbilang(parseInt(angka) % 100);

                } else if (angka < 2000) {

                    return "seribu " + terbilang(parseInt(angka) - 1000);

                } else if (angka < 1000000) {

                    return this.terbilang(Math.floor(parseInt(angka) / 1000)) + " ribu " + this.terbilang(parseInt(angka) % 1000);

                } else if (angka < 1000000000) {

                    return this.terbilang(Math.floor(parseInt(angka) / 1000000)) + " juta " + this.terbilang(parseInt(angka) % 1000000);

                } else if (angka < 1000000000000) {

                    return this.terbilang(Math.floor(parseInt(angka) / 1000000000)) + " milyar " + this.terbilang(parseInt(angka) % 1000000000);

                } else if (angka < 1000000000000000) {

                    return this.terbilang(Math.floor(parseInt(angka) / 1000000000000)) + " trilyun " + this.terbilang(parseInt(angka) % 1000000000000);

                }

        },
        async profilePerusahaan() {
            let {data} = await axios.get('/user/profile');
            if (data.code === 200) {
                document.title = data.data.perusahaan.nama;
                const favicon = document.getElementById('favicon');
                favicon.href = data.data.perusahaan.icon;
                return data.data.perusahaan;
            }
        }
    }
})
