<template>
    <div v-if="dataRef.produksi!=null">
        <div class="flex items-center justify-between">
            <h2 class="title flex space-x-2">
                <div>REGISTER PRODUKSI</div>
                <div class="italic text-sm animate-bounce" v-if="dataRef.produksi!=null">
                    <i class="fa fa-warning mr-1"></i>
                    {{dataRef.produksi.tgl_selesai?'Selesai':"Menunggu Proses Produksi"}}
                </div>
            </h2>
            <span v-if="dataRef.produksi!=null">Id Produksi : {{dataRef.produksi.id}}</span>
        </div>
        <div class="py-4 space-y-2">
            <form @submit.prevent="addList" class="flex space-x-1">
                <div class="w-32">
                    <label for="">Id</label>
                    <div class="flex">
                        <input type="text" class="flex-1 border-basic-300 bg-gray-300" v-model="form.id" readonly>
                        <a href="" @click.prevent="dialogBarang.showDialog()"
                           class="px-2 flex items-center justify-center border border-basic-300"><i
                                class="fa fa-folder"></i></a>
                    </div>
                </div>
                <div class="flex-1">
                    <label for="">Bahan Baku</label>
                    <input type="text" readonly class="border-basic-300 bg-gray-300" v-model="form.nama_barang">
                </div>
                <div>
                    <label for="">Satuan</label>
                    <select class="border-basic-300" v-model="form.satuan">
                        <option v-for="it in form.barang_satuan" :value="it.satuan">{{it.satuan}}</option>
                    </select>
                </div>
                <div class="w-32" v-if="!form.type_jasa">
                    <label for="">Stock</label>
                    <input type="text" class="border-basic-300" v-model="form.sisa_stock">
                </div>
                <div class="w-32" v-if="form.type_jasa">
                    <label for="">Harga</label>
                    <input type="number" class="border-basic-300" v-model="form.pokok">
                </div>
                <div class="w-32">
                    <label for="">Jumlah</label>
                    <div class="flex">
                        <input type="number" step="any" ref="jumlahRef" class="flex-1 border-basic-300"
                               v-model="form.jumlah">
                        <button type="submit" class="px-2 flex items-center justify-center border border-basic-300"><i
                                class="fa fa-arrow-right"></i></button>
                    </div>
                </div>
            </form>
            <div class="grid-500">
                <dx-data-grid
                        :allow-column-reordering="true"
                        :allow-column-resizing="true"
                        :column-auto-width="true"
                        :show-borders="true"
                        :focused-row-enabled="true"
                        :hover-state-enabled="true"
                        :data-source="dataRef.dataSource">
                    <dx-column data-field="barang_id" width="100"/>
                    <dx-column data-field="barang.nama_barang"/>
                    <dx-column cell-template="cellJumlah"/>
                    <template #cellJumlah="{data:row}">
                        <div class="text-right">
                            <a href="" class="font-semibold cursor-pointer"
                               @click.prevent="dialogJumlah.showDialog(row.data)">{{bsoft.NumberFormat(row.data.jumlah,1)}}</a>
                        </div>
                    </template>
                    <dx-column data-field="satuan"/>
                    <dx-column data-field="harga" data-type="number" format="#,##0"/>
                    <dx-column data-field="total" data-type="number" format="#,##0"/>
                    <dx-column data-field="ppn" data-type="number" format="#,##0"/>
                    <dx-column data-field="total_setelah_ppn" data-type="number" format="#,##0"/>
                    <dx-column cell-template="cellAction"/>
                    <template #cellAction="{data:row}">
                        <a href="" @click.prevent="destroy(row.data.id)"><i class="fa fa-remove"></i></a>
                    </template>
                    <dx-summary>
                        <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                        <dx-total-item column="total_setelah_ppn" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                        <dx-total-item column="ppn" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                    </dx-summary>
                    <dx-paging enabled="false"/>
                    <dx-filter-row :visible="true"/>
                </dx-data-grid>
            </div>
            <form @submit.prevent="simpan" class="grid grid-cols-2 space-x-5 justify-between pt-5"
                  v-if="dataRef.produksi!=null">
                <div>
                    <div class="flex-1">
                        <label for="">Produk Yang Dihasilkan</label>
                        <div class="flex">
                            <input type="text" class="flex-1 border-basic-300 bg-gray-300"
                                   :value="dataRef.produksi.barang.nama_barang"
                                   readonly>
                            <a href="" @click.prevent="dialogProdukDihasilkan.showDialog()"
                               class="px-2 flex items-center justify-center border border-basic-300"><i
                                    class="fa fa-folder"></i></a>
                        </div>
                    </div>
                    <div class="flex-1">
                        <label for="">Estimasi Produksi Diharapkan</label>
                        <input type="number" v-model="estimasi" @keyup="prosesEstimasi">
                    </div>
                    <div class="flex-1">
                        <label for="">Perkiraan Biaya HPP</label>
                        <div class="font-semibold text-3xl border rounded shadow-lg p-3 broder border-basic-300 bg-basic-500 bg-opacity-20 italic">
                            <div class="flex justify-between">
                                <i>Rp.</i> <span>{{bsoft.NumberFormat((dataRef.produksi.estimasi_hpp.toFixed(0)))}}/{{dataRef.produksi.barang.satuan}}</span>
                            </div>
                        </div>
<!--                        <span class="text-sm capitalize italic">Terbilang : <b>{{bsoft.terbilang(0)}}</b></span>-->
                    </div>
                </div>
                <div class="flex-1">
                    <div class="font-semibold underline">RINCIAN AKUMULASI BIAYA PRODUKSI</div>
                    <div>
                        <div class="flex">
                            <div>Varian Bahan</div>
                            <div class="flex-1 text-right font-semibold">{{dataRef.produksi.varian_produksi}} items
                            </div>
                        </div>
                        <div class="flex">
                            <div>Biaya Produksi Pokok</div>
                            <div class="flex-1 text-right font-semibold">Rp.
                                {{bsoft.NumberFormat(dataRef.produksi.total,0)}}
                            </div>
                        </div>
                        <div class="flex">
                            <div>PPN</div>
                            <div class="flex-1 text-right font-semibold">Rp.
                                {{bsoft.NumberFormat(dataRef.produksi.ppn,0)}}
                            </div>
                        </div>
                        <div class="flex">
                            <div>Total Setelah PPN</div>
                            <div class="flex-1 text-right font-semibold">Rp.
                                {{bsoft.NumberFormat(dataRef.produksi.total_setelah_ppn,0)}}
                            </div>
                        </div>
                        <div class="flex">
                            <div>Target Estimasi</div>
                            <div class="flex-1 text-right font-semibold">{{dataRef.produksi.estimasi}}
                                {{dataRef.produksi.barang.satuan}}
                            </div>
                        </div>
                        <div class="flex">
                            <div>Dengan Estimasi HPP</div>
                            <div class="flex-1 text-right font-semibold">
                                {{bsoft.NumberFormat(dataRef.produksi.estimasi_hpp)}}/{{dataRef.produksi.barang.satuan}}
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-1 mt-2 justify-end border-t border-dashed border-basic-400 pt-2">
                        <button type="button" class="bg-basic-300"
                                @click.prevent="$router.push({name:'produksi.daftar'})"><i class="fa fa-arrow-left"></i>
                            Batal
                        </button>
                        <button type="submit" class="bg-basic-300"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </div>
            </form>
        </div>
        <pilih-barang ref="dialogBarang" @setBarang="setBarang"/>
        <pilih-produk-dihasilkan :produk_only="true" ref="dialogProdukDihasilkan" @setBarang="setProdukDihasilkan"/>
        <produksi-update-quantity ref="dialogJumlah"  @onRefresh="newTransaksi"/>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted, computed} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging, DxSummary, DxTotalItem} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import PilihBarang from '../../../components/pages-admin/PilihBarang.vue';
    import PilihProdukDihasilkan from '../../../components/pages-admin/PilihBarang.vue';
    import * as Swal from "sweetalert2";
    import {useRoute, useRouter} from "vue-router";
    import ProduksiUpdateQuantity from './ProduksiUpdateQuantity.vue';

    const bsoft = useBoedysoft();
    const estimasi = ref(1);
    const route = useRoute();
    const dataRef = reactive({
        dataSource: null,
        produksi: {
            estimasi_hpp: 0,
            barang: {}
        }
    });
    const form = reactive({
        barang_satuan: {}
    });
    const router = useRouter();
    const dialogBarang = ref();
    const dialogProdukDihasilkan = ref();
    const jumlahRef = ref();
    const dialogJumlah = ref();
    const list_satuan = ref([]);

    function newTransaksi() {
        if (route.params.id) {
            bsoft.axios.get('/produksi/edit/' + useRoute().params.id).then(res => {
                if (res.data.code === 200) {
                    dataRef.produksi = res.data.data;
                    console.log(dataRef.produksi);
                    estimasi.value=dataRef.produksi.estimasi;
                    refresh();
                }
            })
        } else {
            bsoft.axios.get('/produksi/generate').then(res => {
                if (res.data.code === 200) {
                    dataRef.produksi = res.data.data;
                    estimasi.value=dataRef.produksi.estimasi;
                    refresh();
                }
            })
        }
    }

    function clearForm() {
        Object.assign(form, {
            id: null,
            nama_barang: null,
            satuan: null,
            sisa_stock: null,
            pokok: null,
            jumlah: null
        })
    }

    function setProdukDihasilkan(id) {
        bsoft.axios.get('/barang/' + id).then(res => {
            if (res.data.code === 200) {
                Object.assign(dataRef.produksi.barang, res.data.data);
                bsoft.axios.post('/produksi/update/' + dataRef.produksi.id, {barang_id: id}).then(res => {
                    refresh();
                })
            }
        })
    }

    function addList() {
        if (form.jumlah == null || form.jumlah <= 0) {
            Swal.fire({
                icon: "warning",
                title: "Maaf anda belum memilih bahan baku..!",
                text: "Silakan memilih bahan baku dan menentukan jumlah pemakaian..!"
            });
        } else {
            if (!form.type_jasa && form.sisa_stock < form.jumlah) {
                Swal.fire({
                    icon: "warning",
                    title: 'Maaf Stock tidak mencukupi..?' ,
                    text: "Mohon check kembali stock bahan baku anda..?"
                })
            } else {
                let find = dataRef.produksi.produksi_detail.find(e => e.barang_id === form.id);
                if (!find) {
                    bsoft.axios.post('/produksi/detail/add/' + dataRef.produksi.id, form).then(res => {
                        if (res.data.code === 200) {
                            newTransaksi();
                            clearForm();
                        }
                    })
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Bahan Ada Pada list..!",
                        text: "Anda dapat menghapus bahan terlebih dahulu untuk dapat memposting kembali..!"
                    });
                }
            }
        }
    }

    function setBarang(id) {
        bsoft.axios.get('/barang/' + id).then(res => {
            if (res.data.code === 200) {
                Object.assign(form, res.data.data);
                jumlahRef.value.focus();
            }
        })
    }

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                return dataRef.produksi.produksi_detail;
            }
        });
    }

    function destroy(id) {
        bsoft.axios.delete('/produksi/detail/destroy/' + id).then(res => {
            if (res.data.code === 200) newTransaksi();
        })
    }

    function prosesEstimasi() {
        dataRef.produksi.estimasi = estimasi.value;
        dataRef.produksi.estimasi_hpp = dataRef.produksi.total_setelah_ppn / dataRef.produksi.estimasi;
    }

    function simpan() {
        bsoft.axios.post('/produksi/update/' + dataRef.produksi.id, {
            estimasi: estimasi.value,
            barang_id: dataRef.produksi.barang.id,
            finish: true
        }).then(res => {
            if (res.data.code === 200) {
                Swal.fire({
                    icon: "success",
                    title: "Sukses Membuat Produksi..!",
                    text: res.data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    showCancelButton: false
                }).then(() => router.push({name: 'produksi.daftar'}));
            }
        })
    }

    onMounted(newTransaksi)

</script>

<style scoped>

</style>
