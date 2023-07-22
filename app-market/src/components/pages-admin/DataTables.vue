<template>
    <DxDataGrid
            :data-source="dataSource"
            :allow-column-reordering="true"
            :allow-column-resizing="true"
            :column-auto-width="true"
            :show-borders="true"
            :focused-row-enabled="true"
            :hover-state-enabled="true">
        <template v-for="it in fields" >
            <DxColumn :data-field="it.name" :data-type="it.type" :width="it.width"  :caption="it.alias"/>
        </template>
        <DxColumn cell-template="action" :width="80" caption="Action" data-field="id"/>
        <DxColumnChooser :enabled="chooser"/>
        <DxColumnFixing :enabled="true"/>
        <DxFilterRow :visible="true"/>
        <DxPaging :page-size="12"/>
        <DxPager
                :show-page-size-selector="true"
                :allowed-page-sizes="[2, 12, 20]"
        />
        <DxFilterRow :visible="true"/>
        <template #action="{data}">
            <div class="flex justify-around">
                <a href="" class="dx-icon-edit" @click.prevent="$emit('edit',data.value)"></a>
                <a href="" class="dx-icon-remove" @click.prevent="destory(data.value)"></a>
            </div>
        </template>
    </DxDataGrid>
</template>

<script>
    import CustomStore from "devextreme/data/custom_store";
    import {
        DxDataGrid,
        DxColumn,
        DxColumnChooser,
        DxColumnFixing,
        DxFilterPanel,
        DxFilterRow,
        DxPager,
        DxPaging
    } from "devextreme-vue/data-grid";

    export default {
        props: {
            url: String,
            fields: null,
            chooser: {
                default: false,
                type: Boolean
            }
        },
        components: {
            DxDataGrid,
            DxColumn,
            DxPaging,
            DxPager,
            DxFilterPanel,
            DxColumnChooser,
            DxColumnFixing,
            DxFilterRow
        },
        data() {
            return {
                dataSource: null
            }
        },
        mounted() {
            this.refresh()
        },
        methods: {
            destory(id) {
                this.$swal({
                    title: 'Hapus Data ini ?',
                    text: "Anda akan menghapus data secara permanent, Lanjutkan..?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus sekarang!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.$emit('destory', id)
                    }
                })
            },
            refresh() {
                this.dataSource = new CustomStore({
                    key: 'id',
                    load: async () => {
                        const {data} = await this.axios.get(this.url)
                        return data.data;
                    },
                });
            }
        }
    }
</script>

<style scoped>

</style>
