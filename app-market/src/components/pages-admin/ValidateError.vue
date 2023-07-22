<template>
    <div class="text-basic-red text-sm" v-if="props.errors[props.name]">
        <span class="dx-icon-warning"></span> {{props.errors[props.name][0]}}
    </div>
</template>

<script setup>
    import {ref} from 'vue';

    const props = defineProps({
        name: String,
        errors:{}
    })
</script>

