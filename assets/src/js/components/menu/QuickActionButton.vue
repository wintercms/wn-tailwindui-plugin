<template>
    <a
        ref="button"
        :href="item.url"
        :title="item.label"
        class="flex items-center justify-center h-full"
    >
        <img v-if="item.iconSvg" :src="item.iconSvg" class="w-[30px] mr-3 ml-1">
        <i v-else-if="item.icon" :class="'w-[30px] text-2xl text-gray-100 ' + item.icon"></i>
    </a>
</template>
<script>
export default {
    props: ['item'],
    mounted() {
        [...this.item.attributes.trim().matchAll(/[\w|\-]*?=".*?"/g)].map((e) => e[0]).forEach((attribute) => {
            if (!attribute) {
                return;
            }

            const parts = attribute.split("=");
            this.$refs.button.setAttribute(parts[0].trim(), parts[1].trim().replace(/^"|"$/, ''));
        });
    }
}
</script>
