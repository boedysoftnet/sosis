import {createRouter, createWebHistory} from 'vue-router'
import {useBoedysoft} from "../stores/boedysoft";
import axios from "axios";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    linkActiveClass: 'active-link',
    linkExactActiveClass: 'exact-active-link',
    routes: [
        {
            path: '/login',
            name: 'owner.login',
            component: () => import('../views/pages-admin/owners/OwnerLogin.vue')
        },
        {
            path: '/user/login',
            name: 'user.login',
            component: () => import('../views/pages-admin/users/UserLogin.vue')
        },
        {
            path: '/', component: () => import('../views/pages-admin/Index.vue'), children: [
                {path: '', name: 'dashboard', component: () => import('../views/pages-admin/dashboards/Dashboard.vue')},
                {
                    path: '/user-daftar',
                    name: 'user.daftar',
                    component: () => import('../views/pages-admin/users/UserDaftar.vue')
                },
                {
                    path: '/barang-kategori',
                    name: 'barang.kategori.daftar',
                    component: () => import('../views/pages-admin/barang-kategori/BarangKategoriDaftar.vue')
                },
                {
                    path: '/barang',
                    name: 'barang.daftar',
                    component: () => import('../views/pages-admin/barang/BarangDaftar.vue')
                },
                {
                    path: '/perusahaan',
                    name: 'perusahaan.daftar',
                    component: () => import('../views/pages-admin/perusahaans/PerusahaanDaftar.vue')
                },
                {
                    path: '/supplier',
                    name: 'supplier.daftar',
                    component: () => import('../views/pages-admin/suppliers/SupplierDaftar.vue')
                },
                {
                    path: '/bank',
                    name: 'bank.daftar',
                    component: () => import('../views/pages-admin/banks/BankDaftar.vue')
                },
                {
                    path: '/pembelian',
                    name: 'pembelian.daftar',
                    component: () => import('../views/pages-admin/pembelians/PembelianDaftar.vue')
                },
                {
                    path: '/pembelian/register',
                    name: 'pembelian.register',
                    component: () => import('../views/pages-admin/pembelians/PembelianRegister.vue')
                },
                {
                    path: '/pembelian/edit/:id',
                    name: 'pembelian.edit',
                    component: () => import('../views/pages-admin/pembelians/PembelianRegister.vue')
                },
                {
                    path: '/akun-perkiraan',
                    name: 'akun-perkiraan.daftar',
                    component: () => import('../views/pages-admin/akun_perkiraans/AkunPerkiraanDaftar.vue')
                },
                {
                    path: '/neraca-saldo',
                    name: 'neraca-saldo.daftar',
                    component: () => import('../views/pages-admin/accountings/NeracaSaldoDaftar.vue')
                },
                {
                    path: '/barang/masuk',
                    name: 'barang.masuk.daftar',
                    component: () => import('../views/pages-admin/barang/BarangMasukDaftar.vue')
                },
                {
                    path: '/informasi/barang-expaid',
                    name: 'barang.expaid.daftar',
                    component: () => import('../views/pages-admin/informasis/BarangExpaid.vue')
                },
                {
                    path: '/stock/opname/register',
                    name: 'stock.opname.register',
                    component: () => import('../views/pages-admin/opnames/StockOpnameRegister.vue')
                },
                {
                    path: '/customer/daftar',
                    name: 'customer.daftar',
                    component: () => import('../views/pages-admin/customers/CustomerDaftar.vue')
                },
                {
                    path: '/penjualan/register',
                    name: 'penjualan.register',
                    component: () => import('../views/pages-admin/penjualan/PenjualanRegister.vue')
                },
                {
                    path: '/penjualan/edit/:id',
                    name: 'penjualan.edit',
                    component: () => import('../views/pages-admin/penjualan/PenjualanRegister.vue')
                },
                {
                    path: '/penjualan/daftar',
                    name: 'penjualan.daftar',
                    component: () => import('../views/pages-admin/penjualan/PenjualanDaftar.vue')
                },
                {
                    path: '/barang/keluar',
                    name: 'barang.keluar.daftar',
                    component: () => import('../views/pages-admin/informasis/BarangKeluarDaftar.vue')
                },
                {
                    path: '/penjualan/piutang/history/setoran',
                    name: 'penjualan.piutang.history.setoran',
                    component: () => import('../views/pages-admin/penjualan/PenjualanHistorySetoran.vue')
                },
                {
                    path: '/pembelian/hutang/history/setoran',
                    name: 'pembelian.hutang.history.setoran',
                    component: () => import('../views/pages-admin/pembelians/PembelianHistorySetoran.vue')
                },
                {
                    path: '/departement/daftar',
                    name: 'departement.daftar',
                    component: () => import('../views/pages-admin/departements/DepartementDaftar.vue')
                },
                {
                    path: '/produksi/daftar',
                    name: 'produksi.daftar',
                    component: () => import('../views/pages-admin/produksis/ProduksiDaftar.vue')
                },
                {
                    path: '/produksi/register',
                    name: 'produksi.register',
                    component: () => import('../views/pages-admin/produksis/ProduksiRegister.vue')
                },
                {
                    path: '/produksi/edit/:id',
                    name: 'produksi.edit',
                    component: () => import('../views/pages-admin/produksis/ProduksiRegister.vue')
                },
                {
                    path: '/rugi-laba',
                    name: 'accounting.rugi-laba',
                    component: () => import('../views/pages-admin/accountings/RugiLabaDaftar.vue')
                },
                {
                    path: '/kas/daftar',
                    name: 'kas.daftar',
                    component: () => import('../views/pages-admin/kas/KasDaftar.vue')
                },
            ]
        }
    ]
})

router.beforeEach(((to, from, next) => {
    let key_owner = localStorage.getItem('key_user');
    if (to.name !== 'user.login' && key_owner == null) {
        next({name: 'user.login', replace: true});
    } else {
        useBoedysoft().profilePerusahaan();
        next();
    }
}))

export default router
