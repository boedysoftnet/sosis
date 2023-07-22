<template>
    <div>
        <div class="flex items-center justify-between">
            <h2 class="title">DAFTAR PRODUKSI</h2>
            <a href="" @click.prevent="$router.push({name:'produksi.register'})" class="space-x-1 font-semibold"><i
                    class="fa fa-plus"></i> <span>Buat Produksi</span></a>
        </div>
        <div class="py-4 grid-500">
            <div class="flex space-x-2 mb-2">
                <div class="flex-1">
                    <label for="">Filter Type</label>
                    <select v-model="formFilter.filter_type" @change="refresh">
                        <option value="selesai">Selesai</option>
                        <option value="masih produksi">Masih Produksi</option>
                        <option value="semua">Semua</option>
                    </select>
                </div>
                <div class="flex space-x-2" v-if="formFilter.filter_type!='masih produksi'">
                    <div>
                        <label for="">Dari Tanggal</label>
                        <input type="date" v-model="formFilter.tgl_awal" @change="refresh">
                    </div>
                    <div>
                        <label for="">Sampai</label>
                        <input type="date" v-model="formFilter.tgl_akhir" @change="refresh">
                    </div>
                </div>
            </div>
            <dx-data-grid
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :hover-state-enabled="true"
                    :data-source="dataRef.dataSource">
                <dx-paging enabled="false"/>
                <dx-column data-field="id"/>
                <dx-column data-field="perusahaan"/>
                <dx-column data-field="barang_produksi" cell-template="cellBarang"/>
                <template #cellBarang="{data:row}">
                    <div class="flex space-x-1">
                        <div class="bg-red-500 w-5 h-5 rounded-full shadow" v-if="row.data.status!='Selesai'"></div>
                        <div class="bg-green-500 w-5 h-5 rounded-full shadow" v-if="row.data.status==='Selesai'"></div>
                        <div>{{row.data.barang_produksi}}</div>
                    </div>
                </template>
                <dx-column data-field="user"/>
                <dx-column data-field="tgl_produksi" data-type="date"/>
                <dx-column data-field="estimasi"  data-type="number" format="#,##0"/>
                <dx-column data-field="status"/>
                <dx-column data-field="hasil_produksi"  data-type="number" format="#,##0"/>
                <dx-column data-field="tgl_selesai" data-type="date"/>
                <dx-column data-field="proses"/>
                <dx-column data-field="total" data-type="number" format="#,##0"/>
                <dx-column cell-template="cellAction" caption="#" width="75"/>
                <template #cellAction="{data:row}">
                    <div class="flex justify-around w-full" v-if="!row.data.tgl_selesai">
                        <a href="" @click.prevent="edit(row.data.id)"><i
                                class="fa fa-edit"></i></a>
                        <a href="" @click.prevent="dialogHasilProduksi.showDialog(row.data.id)"><i
                                class="fa fa-check-square-o"></i></a>
                        <a href="" @click.prevent="onRemove(row.data.id)"><i class="fa fa-remove"></i></a>
                    </div>
                    <div class="flex justify-around w-full" v-if="row.data.tgl_selesai">
                        <a href="" @click.prevent="dialogDetail.showDialog(row.data.id)"><i
                                class="fa fa fa-newspaper-o text-xl"></i></a>
                        <a href="" @click.prevent="onRemoveProduksi(row.data.id)"><i class="fa fa-remove"></i></a>
                    </div>
                </template>
                <dx-summary>
                    <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                    <dx-total-item column="hasil_produksi" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                    <dx-total-item column="estimasi" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                </dx-summary>
                <dx-filter-row :visible="true"/>
            </dx-data-grid>
            <produksi-hasil ref="dialogHasilProduksi" @onRefresh="refresh"/>
            <produksi-rincian ref="dialogDetail" @onRefresh="refresh"/>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging, DxSummary, DxTotalItem} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import ProduksiHasil from './ProduksiHasil.vue';
    import * as Swal from "sweetalert2";
    import {useRouter} from "vue-router";
    import ProduksiRincian from './ProduksiRincian.vue';

    const bsoft = useBoedysoft();
    const formFilter = reactive({
        filter_type: 'masih produksi',
        tgl_awal: bsoft.DateFormat(Date.now()),
        tgl_akhir: bsoft.DateFormat(Date.now()),
    });
    const dataRef = reactive({
        dataSource: null
    });
    const router = useRouter();
    const dialogHasilProduksi = ref();
    const dialogDetail = ref();

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/produksi', {params: formFilter});
                if (data.code === 200) return data.data;
            }
        });
    }

    onMounted(refresh)

    function edit(id) {
        router.push({name: 'produksi.edit', params: {id}});
    }

    function onRemove(id) {
        Swal.fire({
            icon: "warning",
            title: "Yakin Dihapus..?",
            text: "Anda akan membatalkan produksi ini..?",
            showCancelButton: true
        }).then(result => {
            if (result.isConfirmed) {
                bsoft.axios.delete('/produksi/' + id).then(res => {
                    if (res.data.code === 200) Swal.fire({
                        icon: "success",
                        title: "Data Produksi berhasil dihapus..!",
                        timer: 1000,
                        showConfirmButton: false
                    }).then(refresh);
                })
            }
        })
    }

    function onRemoveProduksi(id){
      Swal.fire({
        icon: "warning",
        title: "Yakin Dihapus..?",
        text: "Anda akan membatalkan produksi ini, semua stock akan dikembalikan..?",
        showCancelButton: true
      }).then(result => {
        if (result.isConfirmed) {
          bsoft.axios.delete('/produksi/delete/' + id).then(res => {
            if (res.data.code === 200) Swal.fire({
              icon: "success",
              title: "Data Produksi berhasil dihapus..!",
              timer: 1000,
              showConfirmButton: false
            }).then(refresh);
          })
        }
      })
    }

</script>

<style scoped>

</style>
