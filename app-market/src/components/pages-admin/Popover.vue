<template>
    <div v-if="dialogNotif" class="shadow border rounded bg-basic-500 bg-opacity-30 mt-2 absolute w-1/3">
        <div>
            <h2 class="p-3 bg-basic-600 text-white font-semobold uppercase flex items-center space-x-2">
                <fa-icon icon="warning"></fa-icon>
                <span>Informasi Menarik</span></h2>
        </div>
        <div class="p-3">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Et exercitationem optio similique suscipit
            vero? Aperiam odit quasi totam. Animi eligendi ex in nostrum omnis, optio porro qui quidem sequi totam.
        </div>
        <div class="border-t border-dashed border-basic-600 py-2">
            <button class="bg-basic-200 font-semibold shadow" @click.prevent="dismisPopup">Okey Siap</button>
        </div>
    </div>
</template>

<script setup>
    import {ref} from 'vue';

    const dialogNotif = ref(false);

    function showPopup() {
        dialogNotif.value = true;
    }

    function dismisPopup() {
        dialogNotif.value = false;
    }

    defineExpose({
        showPopup, dismisPopup
    })
</script>

<style scoped>

</style>
