<template>
    <div class="w-full">
        <div class="border shadow p-3 mt-10">
            <dx-chart
                    id="chart"
                    palette="Harmony Light"
                    title="STATISTIK PENJUALAN"
                    :data-source="dataRef.dataSource">
                <dx-series
                        name="Barang"
                        argument-field="nama"
                        value-field="jumlah"
                        type="line"
                        color="orange">
                    <dx-label
                            position="top"
                            :visible="true"
                            background-color="#c18e92"
                            format="#,##0"
                    />
                </dx-series>
                <dx-legend
                        vertical-alignment="top"
                        horizontal-alignment="center"/>
            </dx-chart>
        </div>
        <div class="p-3 border rounded shadow mt-2">
            <label for="">FILTER PERIODE GRAFIK</label>
            <div class="flex space-x-2">
                <input type="date" v-model="form.tgl_awal" @change="refresh">
                <input type="date" v-model="form.tgl_akhir" @change="refresh">
            </div>
        </div>
        <div class="text-green-600 font-semibold mt-2">
            NB : Anda dapat merequest informasi grafik yang ingin ditampilkan..!
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";

    const bsoft = useBoedysoft();

    const form=reactive({
        tgl_awal:bsoft.FirstDate(Date.now()),
        tgl_akhir:bsoft.LastDate(Date.now()),
    })
    const dataRef = reactive({
        dataSource: null
    });

    import DxChart, {
        DxArgumentAxis,
        DxExport,
        DxSeries,
        DxValueAxis,
        DxLabel,
        DxLegend,
        DxTick,
    } from 'devextreme-vue/chart';

    function refresh() {
        bsoft.axios.get('/penjualan/statistik/barang',{params:form}).then(res => {
            if (res.data.code === 200) dataRef.dataSource = res.data.data;
        })
    }
    onMounted(refresh);
</script>

<style>
    #grafik{
        width: 100%;
    }
</style>
