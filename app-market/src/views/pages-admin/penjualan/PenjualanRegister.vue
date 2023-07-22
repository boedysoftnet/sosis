<template>
    <div class="">
        <div class="flex items-center justify-between">
            <h2 class="title uppercase font-semibold">Transaksi Penjualan <span class="font-semibold">Kasir : {{dataRef.penjualan.nama_operator}}</span>
            </h2>
            <span class="font-semibold text-xl">ID : {{dataRef.penjualan.id}}</span>
        </div>
        <div class="flex-1 flex h-full flex-col">
            <form @submit.prevent="addList" class="flex space-x-2 p-3 bg-basic-100 mt-2 rounded shadow">
                <div class="w-32">
                    <label for="">Id Barang</label>
                    <div class="flex">
                        <input type="text" v-model="form.id" v-on:keyup.13="dialog.showDialog">
                        <button type="button" class="border bg-basic-300 dark:text-white dark:bg-black"
                                @click.prevent="dialogBarang.showDialog">
                            <fa-icon icon="folder"></fa-icon>
                        </button>
                    </div>
                </div>
                <div class="flex-1">
                    <label for="">Nama Barang</label>
                    <input type="text" v-model="form.nama_barang" readonly class="bg-gray-200">
                </div>
                <div class="w-32">
                    <label for="">Satuan</label>
                    <select v-model="form.satuan_isi" @change="pilihSatuan">
                        <option v-for="it in dataRef.satuan" :value="it.isi">{{it.satuan}}{{it.isi!=1?'/' +
                            it.isi:''}}
                        </option>
                    </select>
                </div>
                <div class="w-32">
                    <label for="">Stock</label>
                    <input type="text" v-model="form.sisa_stock" readonly class="bg-gray-200">
                </div>
                <div class="w-32">
                    <label for="">Jumlah</label>
                    <div class="flex">
                        <input type="number" v-model="form.qty" ref="qtyRef">
                        <button class="border bg-basic-300 dark:text-white dark:bg-black">
                            <fa-icon icon="plus"></fa-icon>
                        </button>
                    </div>
                </div>
            </form>
            <div class="p-3 bg-basic-100 mt-3 grid-300">
                <dx-data-grid
                        :focused-row-enabled="true"
                        :allow-column-reordering="true"
                        :allow-column-resizing="true"
                        :column-auto-width="true"
                        :show-borders="true"
                        :data-source="dataRef.dataSource">
                    <dx-column data-field="barang_id"/>
                    <dx-column data-field="barang.nama_barang"/>
                    <dx-column data-field="harga" data-type="number" format="#,##0"/>
                    <dx-column data-field="qty_satuan" format="#,###.0" cell-template="cellQty"/>
                    <template #cellQty="{data:row}">
                        <a href="" @click.prevent="dialogQuantity.showDialog(row.data)"
                           class="text-basic-500 font-semibold">{{row.data.qty_satuan}}</a>
                    </template>
                    <dx-column data-field="satuan"/>
                    <dx-column data-field="sub_total" data-type="number" format="#,##0"/>
                    <dx-column data-field="diskon" data-type="number" format="#,##0"/>
                    <dx-column data-field="total" data-type="number" format="#,##0"/>
                    <dx-column data-field="ppn" data-type="number" format="#,##0"/>
                    <dx-column data-field="total_setelah_ppn" data-type="number" format="#,##0"/>
                    <dx-column cell-template="cellAction" caption="#"/>
                    <template #cellAction="{data:row}">
                        <div class="text-center">
                            <a href="" @click.prevent="destoryItem(row.data.id)">
                                <fa-icon icon="remove"></fa-icon>
                            </a>
                        </div>
                    </template>
                    <dx-summary>
                        <dx-total-item column="sub_total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                        <dx-total-item column="diskon" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                        <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                        <dx-total-item column="qty_satuan" summary-type="sum" display-format="{0}"
                                       value-format="#,##0"/>
                        <dx-total-item column="ppn" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                        <dx-total-item column="total_setelah_ppn" summary-type="sum" display-format="{0}"
                                       value-format="#,##0"/>
                    </dx-summary>
                </dx-data-grid>
                <pilih-barang ref="dialogBarang" @setBarang="setBarang"/>
            </div>
            <div class="p-3 border shadow">
                <label for="">Catatan</label>
                <textarea cols="30" v-model="transaksi.catatan" rows="2"
                          placeholder="Berikan catatan apabila diperlukan..!"></textarea>
            </div>
            <div class="mt-3 flex space-x-2">
                <div class="flex-1 p-3 bg-basic-200">
                    <div class="border-b border-basic-300 border-dashed">
                        <label for="">Member :</label>
                        <div class="text-2xl font-semibold uppercase">
                            {{dataRef.penjualan.customer.nama}}/{{dataRef.penjualan.customer.telpon}}
                        </div>
                    </div>
                    <div class="font-semibold">
                        <label for="">Total Bayar :</label>
                        <div class="text-2xl font-semibold text-basic-700">
                            Rp. {{bsoft.NumberFormat(dataRef.penjualan.total_setelah_ppn)}}
                        </div>
                    </div>
                </div>
                <div class="bg-basic-200 p-3 flex-1">
                    <div class="border-b border-dashed border-basic-800">
                        <h2 class="font-semibold uppercase">Akumulasi Biaya</h2>
                    </div>
                    <div>
                        <div class="flex items-center">
                            <label for="" class="flex-1">Sub Total</label>
                            <div class="font-semibold">{{bsoft.NumberFormat(dataRef.penjualan.sub_total)}}</div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center">
                            <label for="" class="flex-1">Diskon</label>
                            <div class="font-semibold">{{bsoft.NumberFormat(dataRef.penjualan.diskon)}}</div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center">
                            <label for="" class="flex-1">Total</label>
                            <div class="font-semibold">{{bsoft.NumberFormat(dataRef.penjualan.total)}}</div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center">
                            <label for="" class="flex-1">PPN</label>
                            <div class="font-semibold">{{bsoft.NumberFormat(dataRef.penjualan.ppn)}}</div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center">
                            <label for="" class="flex-1">Total Setelah PPN</label>
                            <div class="font-semibold">{{bsoft.NumberFormat(dataRef.penjualan.total_setelah_ppn)}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 flex justify-between items-center space-x-2">
                <div class="space-x-1">
                    <button type="button" class="bg-basic-400 text-white"
                            @click.prevent="dialogDiskon.showDialog(dataRef.penjualan.id)">
                        <fa-icon icon="percentage"></fa-icon>
                        Set Diskon
                    </button>
                    <button type="button" class="bg-basic-400 text-white"
                            @click.prevent="dialogCustomer.showDialog">
                        <fa-icon icon="user"></fa-icon>
                        Set Member
                    </button>
                </div>
                <div class="flex space-x-1">
                    <button type="button" class="bg-basic-400 text-white"
                            @click.prevent="$router.push({name:'dashboard'})">
                        <fa-icon icon="arrow-left"></fa-icon>
                        Back
                    </button>
                    <button type="button" class="bg-basic-400 text-white" @click.prevent="simpanPreorder">
                        <fa-icon icon="folder"></fa-icon>
                        Preorder
                    </button>
                    <button type="button" class="bg-basic-400 text-white"
                            @click.prevent="dialogFinish.showDialog(dataRef.penjualan.id)">
                        <fa-icon icon="save"></fa-icon>
                        Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
    <penjualan-update-quantity ref="dialogQuantity" @onRefresh="newTransaksi"></penjualan-update-quantity>
    <penjualan-diskon-dialog ref="dialogDiskon" @onRefresh="newTransaksi"></penjualan-diskon-dialog>
    <pilih-customer ref="dialogCustomer" @setCustomer="setCustomer"></pilih-customer>
    <penjualan-finish ref="dialogFinish" @onRefresh="newTransaksi"></penjualan-finish>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging, DxTotalItem, DxSummary} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import PilihBarang from '../../../components/pages-admin/PilihBarang.vue';
    import * as Swal from "sweetalert2";
    import PenjualanUpdateQuantity from './PenjualanUpdateQuantity.vue';
    import PenjualanDiskonDialog from './PenjualanDiskonDialog.vue';
    import {useRoute, useRouter} from "vue-router";
    import PilihCustomer from '../../../components/pages-admin/PilihCustomer.vue';
    import PenjualanFinish from './PenjualanFinish.vue';

    const bsoft = useBoedysoft();
    const meta = {
        id: null,
        nama_barang: null,
        satuan: null,
        stock: null,
        pokok: null,
        harga_jual: null,
        qty: null
    }
    const transaksi = reactive({
        transaksi: {}
    })
    const form = reactive({...meta})
    const dataRef = reactive({
        dataSource: null,
        penjualan: {
            customer: {}
        },
        satuan: {}
    })
    const dialogBarang = ref();
    const qtyRef = ref();
    const dialogQuantity = ref();
    const dialogDiskon = ref();
    const dialogCustomer = ref();
    const dialogFinish = ref();
    const router = useRouter();
    const route = useRoute();

    async function newTransaksi() {
        let id = route.params.id;
        let url = id ? `/penjualan/edit/${id}` : `/penjualan/generate`;
        let res = await bsoft.axios.post(url);
        if (res.data.code === 200) {
            if (res.data.data.status === 'Finish') router.push({name: 'penjualan.daftar'});
            dataRef.penjualan = res.data.data;
            Object.assign(form, meta);
            refresh();
        }

    }

    function setCustomer(id) {
        bsoft.axios.post(`/penjualan/customer/${dataRef.penjualan.id}`, {customer_id: id}).then(res => {
            if (res.data.code === 200) {
                newTransaksi();
            }
        })
    }

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                return dataRef.penjualan.penjualan_detail;
            }
        })
    }

    function checkBarang() {
        if (dataRef.penjualan.penjualan_detail.length > 0) {
            let check = dataRef.penjualan.penjualan_detail.filter(e => e.barang_id === form.id);
            console.log(check);
            if (check.length > 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Peringatan.!",
                    text: `Maaf ${check[0].barang.nama_barang} sudah ada dalam keranjang, mohon check kembali.. Anda dapat menghapus data pada keranjang terlebih dahulu untuk mengulang pilihan..!`
                })
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    function addList() {
        if (checkBarang()) {
            if (form.sisa_stock >= form.qty) {
                bsoft.axios.post(`/penjualan/addlist/${dataRef.penjualan.id}`, form).then(res => {
                    if (res.data.code === 200) newTransaksi();
                })
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan..!',
                    text: 'Maaf jumlah stock tidak mencukupi..!',
                })
            }
        }
    }

    function setBarang(id) {
        bsoft.axios.get(`/barang/satuan/${id}`).then(res => {
            if (res.data.code === 200) {
                Object.assign(form, res.data.data.data);
                dataRef.satuan = res.data.data.satuan;
                form.satuan_isi = dataRef.satuan[0].isi;
                qtyRef.value.focus();
            }
        })
    }

    function pilihSatuan() {
        form.sisa_stock = Math.round((form.stock[0].stock / form.satuan_isi) - 0.49);
        bsoft.axios.get(`/barang/satuan/${form.id}`).then(res => {
            if (res.data.code === 200) {
                let it = res.data.data.satuan.filter(e => e.isi == form.satuan_isi)[0];
                form.satuan = it.satuan;
                form.harga_jual = it.harga;
                console.log(it);
            }
        });
        /*todo lanjutkan untuk session penjualan kejar masadepan mu sampai menjadi yang terbaik di bidangnya*/
    }

    function destoryItem(id) {
        bsoft.axios.delete(`/penjualan/destory/${id}`).then(res => {
            if (res.data.code === 200) newTransaksi();
        })
    }

    function simpanPreorder() {
        if (dataRef.penjualan.penjualan_detail.length > 0) {
            bsoft.axios.post(`/penjualan/preorder/${dataRef.penjualan.id}`, transaksi).then(res => {
                if (res.data.code === 200) Swal.fire({
                    icon: "success",
                    title: "Trimakasih..!",
                    text: res.data.message,
                    showConfirmButton: false,
                    timer: 400
                }).then(() => {
                    newTransaksi();
                    router.push({name: 'penjualan.daftar'});
                });
            })
        } else {
            Swal.fire({
                icon: "warning",
                title: "Peringatan..!",
                text: "Maaf keranjang anda masih kosong..!",
            })
        }
    }

    onMounted(() => {
        newTransaksi();
        window.addEventListener("keydown", (e) => {
            if (e.key === "F5") {
                dialogBarang.value.showDialog();
                e.preventDefault();
            }
            if (e.key === 'Escape') {
                dialogBarang.value.dismiss();
                e.preventDefault();
            }
        })
    })

</script>

<style scoped>

</style>
