<template>
    <div class="boedysoft flex flex-col h-min-screen">
        <navbar></navbar>
        <div class="p-5 flex items-center h-[500px]">
            <penjualan-grafik/>
        </div>
    </div>
</template>

<script setup>
    import DxChart, {
        DxArgumentAxis,
        DxCommonSeriesSettings,
        DxLabel,
        DxLegend,
        DxSeries,
        DxTooltip,
        DxValueAxis,
        DxConstantLine,
    } from 'devextreme-vue/chart';
    import PenjualanGrafik from './PenjualanGrafik.vue';

    import {complaintsData} from './data.js';

    import {useRouter} from "vue-router";
    import navbar from '../../../components/pages-admin/Navbar.vue';

    const data = complaintsData.sort((a, b) => b.count - a.count);
    const totalCount = data.reduce((prevValue, item) => prevValue + item.count, 0);
    let cumulativeCount = 0;

    const dataSource = data.map((item) => {
        cumulativeCount += item.count;
        return {
            complaint: item.complaint,
            count: item.count,
            cumulativePercentage: Math.round((cumulativeCount * 100) / totalCount),
        };
    });

    function customizeTooltip(pointInfo) {
        return {
            html: `<div><div class='tooltip-header'>${
                pointInfo.argumentText
            }</div><div class='tooltip-body'><div class='series-name'><span class='top-series-name'>${
                pointInfo.points[0].seriesName
            }</span>: </div><div class='value-text'><span class='top-series-value'>${
                pointInfo.points[0].valueText
            }</span></div><div class='series-name'><span class='bottom-series-name'>${
                pointInfo.points[1].seriesName
            }</span>: </div><div class='value-text'><span class='bottom-series-value'>${
                pointInfo.points[1].valueText
            }</span>% </div></div></div>`,
        };
    }

</script>

<style>
    #chart {
        width: 100%;
    }

    .tooltip-header {
        margin-bottom: 5px;
        font-size: 16px;
        font-weight: 500;
        padding-bottom: 5px;
        border-bottom: 1px solid #c5c5c5;
    }

    .tooltip-body {
        width: 170px;
    }

    .tooltip-body .series-name {
        font-weight: normal;
        opacity: 0.6;
        display: inline-block;
        line-height: 1.5;
        padding-right: 10px;
        width: 126px;
    }

    .tooltip-body .value-text {
        display: inline-block;
        line-height: 1.5;
        width: 30px;
    }
</style>

