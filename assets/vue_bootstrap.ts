import {createApp} from 'vue';

// @ts-ignore
const context = import.meta.webpackContext('./vue/components', {
    recursive: true,
    regExp: /\.vue$/,
});

const components: { [key: string]: any } = {};
context.keys().forEach((path: any) => {
    const name = path.match(/\.\/(.*)\.vue$/)[1];
    components[name] = context(path).default;
});

document.querySelectorAll('[data-vue-component]').forEach((el) => {
    try {
        const name = (el as HTMLElement).dataset.vueComponent;
        if (!name) return;
        const component = components[name];
        if (!component) {
            console.warn(`Vue component "${name}" not found in assets/vue/components`);
            return;
        }

        const originHtml = el.innerHTML;
        const props = (el as HTMLElement).dataset.vueProps ? JSON.parse(<string>(el as HTMLElement).dataset.vueProps) : {};
        createApp(component, {originHtml, ...props}).mount(el);

        el.setAttribute('data-vue-mounted', 'true');
    } catch (e) {
        console.error(e);
    }
});
