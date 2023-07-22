<template>
    <div class="flex items-center" v-if="perusahaan && props.horizontal">
        <img :src="perusahaan.icon" alt="" class="w-32">
        <div class="text-left ml-2">
            <h2 class="font-semibold text-2xl">{{perusahaan.nama}}</h2>
            <p class="text-sm">{{perusahaan.alamat}}</p>
            <p>Email : {{perusahaan.email}}</p>
            <p>Phone : {{perusahaan.telpon}}</p>
        </div>
    </div>
    <div class="flex items-center justify-center flex-col" v-if="perusahaan && !props.horizontal" >
        <img :src="perusahaan.icon" alt="" class="w-32">
        <div class="text-center">
            <h2 class="font-semibold text-2xl">{{perusahaan.nama}}</h2>
            <div class="flex space-x-2">
                <p class="text-sm">{{perusahaan.alamat}}</p>
                <p>Email : {{perusahaan.email}}</p>
                <p>Phone : {{perusahaan.telpon}}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, onMounted} from 'vue';
    import {useBoedysoft} from "../../stores/boedysoft";

    const perusahaan = ref();
    const props=defineProps(
        {
            horizontal:false
        }
    )

    async function refresh() {
        perusahaan.value = await useBoedysoft().profilePerusahaan();
    }

    onMounted(refresh);
</script>

<style scoped>

</style>
