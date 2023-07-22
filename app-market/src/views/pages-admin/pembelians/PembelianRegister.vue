<template>
    <div class="flex flex-col justify-between h-full">
        <div class="border-b border-basic-300 flex justify-between items-center">
            <h2 class="font-semibold text-basic-700">TRANSAKSI PEMBELIAN</h2>
            <span class="font-semibold">ID TRANSAKSI : {{data.pembelian.id}}</span>
        </div>
        <div class="shadow rounded my-2 py-2 px-2 border border-basic-300  top-0 z-10 bg-basic-200 bg-opacity-20">
            <form class="grid grid-cols-1 md:grid-cols-7 gap-2" @submit.prevent="addList">
                <div>
                    <label for="">Id Barang</label>
                    <div class="flex items-center">
                        <input type="text"
                               class="py-1 px-2 border border-basic-300 focus:border-basic-500 w-full outline-none"
                               v-model="formBarang.id">
                        <button @click.prevent="dialogBarang.showDialog" type="button"
                                class="px-2 py-1 bg-basic-500 text-basic-50 border border-basic-500"><span
                                class="dx-icon-folder"></span></button>
                        <pilih-barang :produk_only="true" ref="dialogBarang" @setBarang="setBarang"></pilih-barang>
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label for="">Nama Barang</label>
                    <input type="text"
                           class="py-1 px-2 border border-basic-300 focus:border-basic-500 w-full outline-none"
                           v-model="formBarang.nama_barang" readonly>
                </div>
                <div>
                    <label for="">Harga</label>
                    <input type="text"
                           class="py-1 px-2 border border-basic-300 focus:border-basic-500 w-full outline-none"
                           v-model="formBarang.pokok">
                </div>
                <div>
                    <label for="">Satuan</label>
                    <input type="text"
                           class="py-1 px-2 border border-basic-300 focus:border-basic-500 w-full outline-none"
                           v-model="formBarang.satuan" readonly>
                </div>
                <div>
                    <label for="">Jumlah</label>
                    <div class="flex items-center">
                        <input type="text"
                               class="py-1 px-2 border border-basic-300 focus:border-basic-500 w-full outline-none"
                               v-model="formBarang.qty" ref="qtyRef">
                        <button class="px-2 py-1 bg-basic-500 text-basic-50 border border-basic-500"><span
                                class="dx-icon-add"></span></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="shadow rounded my-2 py-2 px-2 border border-basic-300 h-full flex flex-col">
            <div class="h-full">
                <dx-data-grid
                        :data-source="data.pembelianDetail"
                        :allow-column-reordering="true"
                        :allow-column-resizing="true"
                        :column-auto-width="true"
                        :show-borders="true"
                        :focused-row-enabled="true"
                        show-scrollbar="onHover"
                        :selection="{ mode: 'single' }"
                        @selection-changed="onSelectionChange"
                        :hover-state-enabled="true">
                    <dx-column data-field="barang.id"/>
                    <dx-column data-field="barang.nama_barang" caption="Nama Barang"/>
                    <dx-column data-field="barang.barang_kategori.kategori" caption="Kategori"/>
                    <dx-column data-field="harga" cell-template="cellHarga"/>
                    <template #cellHarga="{data:row}">
                        <a href="" title="Click Untuk Mengedit Harga..!"
                           @click.prevent="refUpdateHarga.showDialog(row.data)" class="w-full px-2 py-2 font-semibold">{{bsoft.NumberFormat(row.data.harga)}}</a>
                    </template>
                    <dx-column data-field="qty" cell-template="cellQuantity"/>
                    <template #cellQuantity="{data:row}">
                        <a href="" title="Click Untuk Mengedit Qty..!"
                           @click.prevent="refUpdateQuantiy.showDialog(row.data)"
                           class="w-full px-2 py-2 font-semibold">{{row.data.qty}}</a>
                        <!--todo: cari penyebabnya yach-->
                    </template>
                    <dx-column data-field="satuan"/>
                    <dx-column data-field="sub_total" format="#,###"/>
                    <dx-column data-field="diskon" format="#,###"/>
                    <dx-column data-field="total" format="#,###"/>
                    <dx-column data-field="ppn" format="#,###"/>
                    <dx-column format="D M Y" cell-template="cellExpaidDate" caption="Expaid"/>
                    <template #cellExpaidDate="{data:row}">
                        <div class="flex">
                            <h2 class="flex-1" v-if="row.data.expaid">{{bsoft.DateFormat(row.data.expaid,"DD-MM-Y")}}</h2>
                            <h2 class="flex-1" v-else>No expaid</h2>
                            <a href="" @click.prevent="refPembelianexpaidDate.showDialog(row.data)"><fa-icon icon="calendar"></fa-icon></a>
                        </div>
                    </template>
                    <dx-column data-field="id" cell-template="action" caption="#" width="30"/>
                    <template #action="{data}">
                        <a href="" class="dx-icon-remove" @click.prevent="detailRemove(data.value)"></a>
                    </template>
                    <dx-load-panel :enabled="true"/>
                    <dx-scrolling mode="standar"/>
                    <dx-sorting mode="none"/>
                    <dx-paging :enabled="false"/>
                </dx-data-grid>
                <pembelian-expaid-date ref="refPembelianexpaidDate" @onRefresh="refresh"></pembelian-expaid-date>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="order-last md:order-first">
                    <div class="m-0 mt-2 md:m-5 border rounded shadow p-3 border-basic-300 bg-basic-500 bg-opacity-10">
                        <label for="">Total Biaya</label>
                        <div class="font-semibold text-3xl">Rp.
                            {{Intl.NumberFormat().format(data.pembelian.total_setelah_ppn)}}
                        </div>
                    </div>
                </div>
                <div class="mt-2 md:mt-0">
                    <div class="border-b uppercase font-semibold">
                        Akumulasi Biaya :
                    </div>
                    <div class="grid grid-cols-2">
                        <label for="">Sub Total</label>
                        <div class=" text-right">{{Intl.NumberFormat().format(data.pembelian.sub_total)}}</div>
                    </div>
                    <div class="grid grid-cols-2">
                        <label for="">Diskon</label>
                        <div class="text-right">{{Intl.NumberFormat().format(data.pembelian.diskon)}}</div>
                    </div>
                    <div class="grid grid-cols-2">
                        <label for="">Total Sebelum PPN</label>
                        <div class="text-right">{{Intl.NumberFormat().format(data.pembelian.total)}}</div>
                    </div>
                    <div class="grid grid-cols-2">
                        <label for="">PPN ({{data.pembelian.supplier?data.pembelian.supplier.ppn:''}}%)</label>
                        <div class="text-right">{{Intl.NumberFormat().format(data.pembelian.ppn)}}</div>
                    </div>
                    <div class="grid grid-cols-2">
                        <label for="">Total</label>
                        <div class="text-right font-semibold">
                            {{Intl.NumberFormat().format(data.pembelian.total_setelah_ppn)}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="flex flex-col md:flex-row items-center">
                <pembelian-diskon-dialog ref="diskonDialog" @onRefresh="refresh"
                                         :pembelian_id="data.pembelian.id"></pembelian-diskon-dialog>
                <button class="px-2 py-1 bg-yellow-500 text-basic-50 font-semibold flex items-center ml-1"
                        @click.prevent="diskonDialog.showDialog()"><span
                        class="dx-icon-percent mr-2"></span> Set Diskon
                </button>
                <pilih-supplier ref="refSupplier" @setSupplier="setSupplier"></pilih-supplier>
                <button class="px-2 py-1 bg-blue-500 text-basic-50 font-semibold flex items-center ml-1"
                        @click.prevent="refSupplier.showDialog()"><span
                        class="dx-icon-product mr-2"></span> Set Supplier
                </button>
                <pembelian-update-quantity ref="refUpdateQuantiy" @onRefresh="refresh"></pembelian-update-quantity>
                <button class="px-2 py-1 bg-yellow-600 text-basic-50 font-semibold flex items-center ml-1"
                        @click.prevent="refUpdateQuantiy.showDialog(data.pembelianSelection)"><span
                        class="dx-icon-edit mr-2"></span> Edit Qty Item
                </button>
            </div>
            <div class="flex justify-end">
                <button class="px-2 py-1 bg-basic-500 text-basic-50 font-semibold flex items-center ml-1"
                        @click.prevent="router.push({name:'pembelian.daftar'})"><span
                        class="dx-icon-back mr-2"></span> Batal
                </button>
                <button class="px-2 py-1 bg-basic-500 text-basic-50 font-semibold flex items-center ml-1"
                        @click.prevent="simpanPreorder"><span
                        class="dx-icon-orderedlist mr-2"></span> Preorder
                </button>
                <pembelian-finish ref="refPembelianFinish"></pembelian-finish>
                <button class="px-2 py-1 bg-basic-500 text-basic-50 font-semibold flex items-center ml-1"
                        @click.prevent="simpanPembelian"><span
                        class="dx-icon-save mr-2"></span> Simpan Stock
                </button>
                <pembelian-update-harga ref="refUpdateHarga" @onRefresh="refresh"></pembelian-update-harga>
            </div>
        </div>
    </div>
</template>

<script setup>
    /*todo cari untuk bisa scrolling tetap semangat pasti bisa*/
    import {
        DxDataGrid,
        DxLoadPanel,
        DxScrolling,
        DxSorting,
        DxPaging,
        DxColumn,
        DxFilterRow,
        DxSummary,
        DxTotalItem
    } from 'devextreme-vue/data-grid';
    import {reactive, onMounted, ref} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import pilihBarang from '../../../components/pages-admin/PilihBarang.vue';
    import pembelianDiskonDialog from './PembelianDiskonDialog.vue';
    import pilihSupplier from '../../../components/pages-admin/PilihSupplier.vue';
    import pembelianUpdateQuantity from './PembelianUpdateQuantity.vue';
    import pembelianFinish from './PembelianFinish.vue';
    import {useRouter, useRoute} from 'vue-router';
    import pembelianUpdateHarga from './PembelianUpdateHarga.vue';
    import pembelianExpaidDate from './PembelianExpaidDate.vue';

    import Swal from 'sweetalert2';

    const bsoft = useBoedysoft();
    const data = reactive({
        pembelian: {},
        pembelianDetail: null,
        pembelianSelection: {}
    });
    const dialogBarang = ref();
    const diskonDialog = ref();
    const route = useRoute();

    const formBarang = reactive({
        satuan: {}
    });
    const qtyRef = ref();
    const refSupplier = ref();
    const refUpdateQuantiy = ref();
    const refUpdateHarga = ref();
    const router = useRouter();
    const refPembelianFinish = ref();
    const refPembelianexpaidDate = ref();

    function detailRemove(id) {
        bsoft.axios.delete('/pembelian/detail/hapus/' + id).then(res => {
            if (res.data.code === 200) refresh()
        })
    }

    function setBarang(id) {
        bsoft.axios.get('/barang/' + id).then(res => {
            Object.assign(formBarang, res.data.data);
            formBarang.pokok=formBarang.pokok_terakhir;
            qtyRef.value.focus();
        })
    }

    function summaryCustom(data) {
        return Intl.NumberFormat().format(data.value);
    }

    function summaryTotalCustom(data) {
        return 'Total ' + Intl.NumberFormat().format(data.value);
    }

    async function refresh() {
        let id = route.params.id;
        if (id) {
            bsoft.axios.get(`/pembelian/${id}`).then(res => {
                data.pembelian = res.data.data;
                if (data.pembelian.status === 'Finish') router.push({name: 'pembelian.daftar'});
                data.pembelianDetail = new CustomStore({
                    key: 'id',
                    load: async () => {
                        const {data} = await bsoft.axios.get(`/pembelian/detail/${res.data.data.id}`);
                        return data.data;
                    },
                })
            })
        } else {
            bsoft.axios.post('/pembelian/create').then(res => {
                data.pembelian = res.data.data;
                data.pembelianDetail = new CustomStore({
                    key: 'id',
                    load: async () => {
                        const {data} = await bsoft.axios.get(`/pembelian/detail/${res.data.data.id}`);
                        return data.data;
                    },
                })
            })
        }
    }

    function addList() {
        if (formBarang.qty != 0 && formBarang.qty != null) {
            Object.assign(formBarang, {
                harga: formBarang.pokok,
                barang_id: formBarang.id,
                ppn: 0,
                satuan: formBarang.satuan,
                diskon: 0,
                catatan: '-',
                qty: formBarang.qty,
                satuan_isi:1
            })
            bsoft.axios.post('/pembelian/detail/add/' + data.pembelian.id, formBarang).then(res => {
                refresh();
                Object.assign(formBarang, {
                    pokok: 0,
                    ppn: 0,
                    nama_barang: null,
                    kategori: null,
                    id: null,
                    satuan: '',
                    diskon: 0,
                    catatan: '-',
                    qty: null,
                    satuan_isi:1
                })
            })
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Informasi..!',
                text: 'Maaf Jumlah belum ditentukan..!'
            }).then(() => qtyRef.value.focus())
        }
    }

    onMounted(refresh)

    defineExpose({
        setBarang
    })

    function setSupplier(id) {
        console.log(id);
        bsoft.axios.post(`/pembelian/supplier/${data.pembelian.id}`, {supplier_id: id}).then(res => {
            if (res.data.code === 200) refresh();
        })
    }

    function onSelectionChange({selectedRowsData}) {
        data.pembelianSelection = selectedRowsData[0];
    }

    function simpanPreorder() {
        bsoft.axios.post(`/pembelian/preorder/${data.pembelian.id}`).then(res => {
            if (res.data.code === 200) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Trimakasih..!',
                    text: res.data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => router.push({name: 'pembelian.daftar'}))
            }
        })
    }

    function simpanPembelian() {
        if (data.pembelian.sub_total!=0) {
            refPembelianFinish.value.showDialog(data.pembelian.id);
        } else {
            Swal.fire({
                position: 'center',
                icon: 'warning',
                title: 'Peringatan..!',
                text: 'Maaf, List pembelian masih kosong..!',
                showConfirmButton: true,
            });
        }
    }

</script>

<style>

</style>
