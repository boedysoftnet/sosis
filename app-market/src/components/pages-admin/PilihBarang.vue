<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-50 flex items-center justify-center z-10">
            <div class="w-full md:w-1/2 bg-basic-50">
                <div class="py-2 px-2 border-b border-basic-300 font-semibold uppercase flex justify-between items-center">
                    <div><a href="" class="dx-icon-back" @click.prevent="dismiss()"></a> Pilih Barang</div>
                    <a href="" class="dx-icon-remove" @click.prevent="dismiss()"></a>
                </div>
                <div class="py-2 px-2">
                    <div class="mb-2">
                        <input type="search"
                               class="py-1 px-2 border border-basic-300 w-full outline-none focus:border-basic-500"
                               v-model="cari" placeholder="Cari Barang..!">
                    </div>
                    <dx-data-grid :data-source="dataSource.data"
                                  :allow-column-reordering="true"
                                  :allow-column-resizing="true"
                                  :column-auto-width="true"
                                  :show-borders="true"
                                  :selection="{ mode: 'single' }"
                                  @selection-changed="pilih"
                                  :focused-row-enabled="true"
                                  :hover-state-enabled="true">>
                        <dx-column data-field="nama_barang"/>
                        <dx-column data-field="barang_kategori.kategori" caption="Kategori"/>
                        <dx-column data-field="harga_jual" caption="Harga" format="#,###"/>
                        <dx-column data-field="sisa_stock" caption="Stock"/>
                        <dx-column data-field="satuan" caption="Satuan"/>
                        <dx-paging :enabled="false"/>
                    </dx-data-grid>
                    <div class="mt-2 border-t border-basic-300 pt-2 flex items-center justify-between">
                        <button class="py-1 px-2 bg-basic-500 text-basic-50 mr-1"
                                @click.prevent="refBarangRegister.showDialog()"><span class="dx-icon-plus"></span>Buat
                            Barang
                        </button>
                        <button class="py-1 px-2 bg-blue-500 text-basic-50" @click.prevent="dismiss"><span
                                class="dx-icon-back"></span>Tutup
                        </button>
                    </div>
                    <barang-register ref="refBarangRegister" @setBarang="setBarang"></barang-register>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
    import {ref, reactive, watch, onMounted} from 'vue';
    import {
        DxDataGrid,
        DxLoadPanel,
        DxScrolling,
        DxSorting,
        DxPaging,
        DxColumn,
        DxFilterRow,
        DxSummary,
        DxKeyboardNavigation,
        DxTotalItem
    } from 'devextreme-vue/data-grid';
    import barangRegister from '../../views/pages-admin/barang/BarangRegister.vue';

    import {useBoedysoft} from "../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import useDebouncedRef from "../../helpers/debounce";

    const dialog = ref(false);
    const dataSource = reactive({
        data: null
    })
    const bsoft = useBoedysoft();
    const cari = useDebouncedRef('', 400);
    const emit = defineEmits();
    const refBarangRegister = ref();
    const props = defineProps(['produk_only']);

    function showDialog() {
        dialog.value = true
    }

    function refresh() {
        dataSource.data = new CustomStore({
            key: 'id',
            load: async () => {
                let form = {cari: cari.value, limit: 50};
                if(props.produk_only) form = {cari: cari.value, limit: 50,produk_only:props.produk_only};
                let {data} = await bsoft.axios.get('/barang', {params: form});
                return data.data;
            }
        })
    }

    watch(cari, (value) => {
        refresh();
    })

    function dismiss() {
        dialog.value = false
    }

    onMounted(refresh)

    defineExpose({
        showDialog, dismiss
    })

    function setBarang(id) {
        emit('setBarang', id);
        dismiss();
    }

    function pilih({selectedRowsData}) {
        emit('setBarang', selectedRowsData[0].id);
        dismiss();
    }

</script>

<style scoped>

</style>
