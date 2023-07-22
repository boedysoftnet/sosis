<template>
    <div class="flex justify-between border-b pb-1 sticky top-0 bg-white">
        <div class="flex items-center space-x-2 font-semibold">
            <fa-icon icon="dashboard"></fa-icon>
            <span>TECONOLGY SYSTEM POS MANAGEMENT COMPANY</span>
        </div>
        <ul class="flex h-[40px]" data-aos="fade-left">
            <li><a href="" class="block py-2 px-4 transition duration-300 hover:border-b-2 border-indigo-800"
                   @click.prevent="logout"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </div>
</template>

<script setup>
    function logout() {
        localStorage.removeItem('key_user');
        window.location.href = '/';
    }

</script>

<style scoped>

</style>
