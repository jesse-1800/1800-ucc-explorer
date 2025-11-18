<template>
  <div id="gjs"></div>
</template>

<script lang="ts" setup>
import grapesjs from 'grapesjs';
import {storeToRefs} from "pinia";
import 'grapesjs/dist/css/grapes.min.css';
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import pluginForms from 'grapesjs-plugin-forms';
import pluginBasic from 'grapesjs-blocks-basic';
import {my_custom_fields} from "@/composables/GlobalComposables";

const store = GlobalStore();
const emit = defineEmits(['gjs_loaded']);
const {is_gjs_loaded,are_customfields_added} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();

onMounted(() => {
  const token = getAccessTokenSilently();
  store.FetchCustomFields(token).then(() => {
    // Instantiate GrapeJS
    (window as any).gjs_instance = grapesjs.init({
      height: '100vh',
      showOffsets: true,
      noticeOnUnload: false,
      storageManager: false,
      container: '#gjs',
      fromElement: true,
      plugins: [
        'grapesjs-preset-webpage',
        pluginBasic,
        pluginForms,
      ],
      pluginsOpts: {
        'grapesjs-preset-webpage': {},
        'grapesjs-plugin-forms': {},
      },
      canvas: {
        styles: [
          '/src/styles/grapesjs-demo.css?v=' + Date.now(),
        ]
      },
      canvasCss: `
        body {
          transform: scale(1.25);
          transform-origin: top left;
          width: 80%;
        }
      `
    });

    // Set global state for tracking instance load.
    (window as any).gjs_instance.on('load', () => {
      console.log('GrapesJS editor fully loaded!');
      is_gjs_loaded.value = true;
    });

    // Add border width to Decorations in Style Manager
    const editor = (window as any).gjs_instance;
    const sm = editor.StyleManager;
    const decorationsSector = sm.getSectors().find(s => s.get('name').toLowerCase().includes('decoration'));
    sm.addProperty(decorationsSector.get('id'), {
      type: 'composite',
      property: 'border-width',
      label: 'Border Width',
      properties: [
        { type: 'number', units: ['px'], default: '0', property: 'border-top-width', label: 'Top' },
        { type: 'number', units: ['px'], default: '0', property: 'border-right-width', label: 'Right' },
        { type: 'number', units: ['px'], default: '0', property: 'border-bottom-width', label: 'Bottom' },
        { type: 'number', units: ['px'], default: '0', property: 'border-left-width', label: 'Left' }
      ]
    });

    // Add vertical alignment to TD
    sm.addProperty(decorationsSector.get('id'), {
      type: 'select',
      property: 'vertical-align',
      label: 'Vertical Align',
      defaults: 'middle',
      options: [
        { id: 'top', label: 'Top' },
        { id: 'middle', label: 'Middle' },
        { id: 'bottom', label: 'Bottom' },
        { id: 'baseline', label: 'Baseline' }
      ]
    });


    // Add copy and paste to Toolbar
    editor.on('component:selected', () => {
      const commandIcon = 'fa fa-copy';
      const selectedComponent = editor.getSelected();
      const defaultToolbar = selectedComponent.get('toolbar');

      // Check if command already exists to avoid duplicates
      const commandExists = defaultToolbar.some(item => item.attributes?.class === commandIcon);

      if (!commandExists) {
        selectedComponent.set({
          toolbar: [
            ...defaultToolbar,
            {
              attributes: {
                class: commandIcon,
                title: 'Copy to clipboard'
              },
              command: (editor) => {
                const selected = editor.getSelected();
                const classNames = selected.get('classes').map(c => c.get('name'));
                const cssClassRules = editor.CssComposer.getAll().filter(rule =>
                    rule.get('selectors').some(s => classNames.includes(s.get('name')))
                ).map(rule => rule.toJSON());

                const collectInlineCss = (component) => {
                  const id = component.getId();
                  const style = component.getStyle();
                  const styles = [{ id, style }];
                  component.components().forEach(child => {
                    styles.push(...collectInlineCss(child));
                  });
                  return styles;
                };
                const json_data = JSON.stringify({
                  components: selected.toJSON(),
                  inline_css: collectInlineCss(selected),
                  classes_css: cssClassRules,
                });

                localStorage.setItem('gjs-clipboard', json_data);
                store.ShowSuccess("Component copied to clipboard!");
              }
            }
          ]
        });
      }
    });
    editor.on('component:selected', () => {
      const commandIcon = 'mdi mdi-content-paste';
      const selectedComponent = editor.getSelected();
      const defaultToolbar = selectedComponent.get('toolbar');

      // Check if command already exists to avoid duplicates
      const commandExists = defaultToolbar.some(item => item.attributes?.class === commandIcon);

      if (!commandExists) {
        selectedComponent.set({
          toolbar: [
            ...defaultToolbar,
            {
              attributes: {
                class: commandIcon,
                title: 'Paste from clipboard'
              },
              command: () => {
                const clipboardData = localStorage.getItem('gjs-clipboard');
                if (clipboardData) {
                  try {
                    const {components,inline_css,classes_css} = JSON.parse(clipboardData);

                    // Get the currently selected component or use wrapper as fallback
                    const target = editor.getSelected() || editor.getWrapper();

                    // Append the component to the target
                    target.append(components);

                    // Locate IDs
                    const findById = (id) => {
                      const wrapper = editor.getWrapper();
                      const res1 = wrapper.find(`#${id}`);
                      if (res1 && res1.length) return res1[0];
                      return null;
                    };

                    // apply inline css
                    inline_css.forEach(item => {
                      const id = item.id || (item.attributes && item.attributes.id);
                      const comp = findById(id);
                      if (comp) comp.setStyle(item.style);
                    });

                    // Apply selector-based css
                    classes_css.forEach(rule => {
                      const selectors = rule.selectors.map(n => `.${n}`).join(', ');
                      const style = rule.style || {};

                      // Convert style object to CSS string
                      const cssString = Object.entries(style)
                        .map(([prop, value]) => `${prop}: ${value}`)
                        .join('; ');

                      editor.Css.addRules(`${selectors} { ${cssString}; }`);
                    });

                    store.ShowSuccess('Component pasted successfully!');
                  } catch (error) {
                    store.ShowError('Error pasting component:', error);
                  }
                } else {
                  store.ShowError('No component in clipboard');
                }
              }
            }
          ]
        });
      }
    });


    // Basic Components
    (window as any).gjs_instance.BlockManager.add('pdf-page', {
      label: 'Page',
      category: 'Basic',
      media: `<svg fill="#ffffff" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M86.45,23.27h-3.475V90.18c0,0.835-0.677,1.513-1.513,1.513H31.987v3.475c0,0.836,0.677,1.513,1.513,1.513l0.001,0v0h52.95 c0.836,0,1.513-0.677,1.513-1.513V24.782C87.963,23.946,87.286,23.27,86.45,23.27z"></path> <path d="M77.988,85.193V14.807c0-0.836-0.677-1.513-1.513-1.513h-3.475v66.911c0,0.836-0.677,1.513-1.513,1.513H22.011v3.475 c0,0.836,0.677,1.513,1.513,1.513c0,0,0,0,0,0h52.951C77.311,86.706,77.988,86.029,77.988,85.193z"></path> <path d="M68.013,75.218V4.832c0-0.836-0.677-1.513-1.513-1.513H13.55c-0.836,0-1.513,0.677-1.513,1.513v70.386 c0,0.836,0.677,1.513,1.513,1.513H66.5C67.336,76.731,68.013,76.054,68.013,75.218z"></path> </g> </g></svg>`,
      content: () => {
        // This solves the glitch where new page
        // carries the same attributes as first ones
        const class_randomize = Date.now().toString(36);
        return {
          tagName: 'section',
          classes: ['pdf-page', `pdf-page-${class_randomize}`],
          droppable: true,
          components: [],
          style: {
            position: 'relative',
          },
        };
      },
    });
    (window as any).gjs_instance.BlockManager.add('pdf-contract-page', {
      label: 'Contract Page',
      category: 'Basic',
      media: `<svg fill="#ffffff" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M86.45,23.27h-3.475V90.18c0,0.835-0.677,1.513-1.513,1.513H31.987v3.475c0,0.836,0.677,1.513,1.513,1.513l0.001,0v0h52.95 c0.836,0,1.513-0.677,1.513-1.513V24.782C87.963,23.946,87.286,23.27,86.45,23.27z"></path> <path d="M77.988,85.193V14.807c0-0.836-0.677-1.513-1.513-1.513h-3.475v66.911c0,0.836-0.677,1.513-1.513,1.513H22.011v3.475 c0,0.836,0.677,1.513,1.513,1.513c0,0,0,0,0,0h52.951C77.311,86.706,77.988,86.029,77.988,85.193z"></path> <path d="M68.013,75.218V4.832c0-0.836-0.677-1.513-1.513-1.513H13.55c-0.836,0-1.513,0.677-1.513,1.513v70.386 c0,0.836,0.677,1.513,1.513,1.513H66.5C67.336,76.731,68.013,76.054,68.013,75.218z"></path> </g> </g></svg>`,
      content: () => {
        // This solves the glitch where new page
        // carries the same attributes as first ones
        const class_randomize = Date.now().toString(36);
        return {
          tagName: 'section',
          classes: ['pdf-page', 'contract-page', `pdf-page-${class_randomize}`],
          droppable: true,
          components: [],
          style: {
            position: 'relative',
          },
        };
      },
    });
    (window as any).gjs_instance.BlockManager.add('pdf-title', {
      label: 'Title',
      category: 'Basic',
      media: `<svg viewBox="0 0 24 24">
    <path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" />
  </svg>`,
      content: {
        tagName: 'h1',
        type: 'text',
        content: 'Insert your title here',
        style: { },
        droppable: true,
      }
    });
    (window as any).gjs_instance.BlockManager.add('pdf-text', {
      label: 'Text',
      category: 'Basic',
      media: `<svg viewBox="0 0 24 24">
    <path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" />
  </svg>`,
      content: {
        tagName: 'p',
        type: 'text',
        content: 'Insert your text here',
        style: { },
        droppable: true,
      }
    });
    (window as any).gjs_instance.BlockManager.add('pdf-unordered-list', {
      label: 'Unordered List',
      category: 'Basic',
      media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 10.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0-3.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4-7.5h12v2H8V6zm0 5h12v2H8v-2zm0 5h12v2H8v-2z"/></svg>`,
      content: {
        tagName: 'ul',
        components: [{ tagName: 'li', type: 'text', content: 'List item' }],
        droppable: true
      }
    });
    (window as any).gjs_instance.BlockManager.add('pdf-ordered-list', {
      label: 'Ordered List',
      category: 'Basic',
      media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 7h2v2H4V7zm0 5h2v2H4v-2zm0 5h2v2H4v-2zm4-10h12v2H8V7zm0 5h12v2H8v-2zm0 5h12v2H8v-2z"/></svg>`,
      content: {
        tagName: 'ol',
        components: [{ tagName: 'li', type: 'text', content: 'List item' }],
        droppable: true
      }
    });
    (window as any).gjs_instance.BlockManager.add('pdf-link', {
      label: 'Link',
      category: 'Basic',
      media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M3.9 12a5 5 0 0 1 5-5h4v2h-4a3 3 0 0 0 0 6h4v2h-4a5 5 0 0 1-5-5zm7-1h2v2h-2v-2zm6.1-4a5 5 0 0 1 0 10h-4v-2h4a3 3 0 0 0 0-6h-4V7h4z"/></svg>`,
      content: {
        type: 'link',
        content: 'Insert your text here',
        style: { },
        droppable: true,
      }
    });
    (window as any).gjs_instance.BlockManager.add('pdf-image', {
      label: 'Image',
      category: 'Basic',
      media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zm-2 0H5V5h14zm-7-7a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm-5 6l3-4 2.5 3.01L17 13l4 6H5z"/></svg>`,
      content: {
        tagName: 'img',
        type: 'image',
        attributes: { src: 'https://via.placeholder.com/150' },
        style: {
          width: '200px',
          height: 'auto',
        },
      },
    });
    (window as any).gjs_instance.BlockManager.add('pdf-container', {
      label: 'Container',
      category: 'Basic',
      media: `<svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" fill="currentColor"/></svg>`,
      content: {
        droppable: true,
        tagName: 'section',
        style: {},
      },
    });
    (window as any).gjs_instance.BlockManager.add('pdf-table', {
      label: 'Table',
      category: 'Basic',
      media: `<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#ffffff" viewBox="0 0 24 24"><path d="M3 3h18v18H3V3zm2 2v4h14V5H5zm0 6v4h14v-4H5zm0 6v2h14v-2H5z"/></svg>`,
      content: {
        tagName: 'table',
        classes: [],
        style: {
          width: '100%',
          borderCollapse: 'collapse',
        },
        components: [
          {
            tagName: 'thead',
            components: [
              {
                tagName: 'tr',
                components: [
                  { tagName: 'th', droppable: true, type: 'table-cell', content: 'Header 1', style: {} },
                  { tagName: 'th', droppable: true, type: 'table-cell', content: 'Header 2', style: {} },
                  { tagName: 'th', droppable: true, type: 'table-cell', content: 'Header 3', style: {} },
                ],
              },
            ],
          },
          {
            tagName: 'tbody',
            components: [
              {
                tagName: 'tr',
                components: [
                  { tagName: 'td', droppable: true, type: 'table-cell', content: 'Row 1 Col 1', style: {} },
                  { tagName: 'td', droppable: true, type: 'table-cell', content: 'Row 1 Col 2', style: {} },
                  { tagName: 'td', droppable: true, type: 'table-cell', content: 'Row 1 Col 3', style: {} },
                ],
              },
            ],
          },
        ],
      },
    });


    // Shortcodes for Client Details
    (window as any).gjs_instance.BlockManager.add('client-first-name',{
      label: 'Client First Name',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_first_name}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('client-last-name',{
      label: 'Client Last Name',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_last_name}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('client-full-name',{
      label: 'Client Full Name',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_full_name}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('client-email',{
      label: 'Client Email',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_email}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('client-company-name',{
      label: 'Client Company Name',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_company_name}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('client-signature',{
      label: 'Client Signature',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_signature}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('client-signed-date',{
      label: 'Client Signed Date',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_signed_date}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('client-initials',{
      label: 'Client Initials',
      category: 'Client Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{client_initials}</span>`,
    });

    // Shortcodes for Author Details
    (window as any).gjs_instance.BlockManager.add('author-company-name',{
      label: 'Author Company Name',
      category: 'Author Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{author_company_name}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('author-full-name',{
      label: 'Author Full Name',
      category: 'Author Details',
      media: `<svg width="64px" height="64px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{author_full_name}</span>`,
    });

    // Shortcodes for Proposal Details
    (window as any).gjs_instance.BlockManager.add('proposal-title',{
      label: 'Proposal Title',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{proposal_title}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-cover-letter',{
      label: 'Cover Letter',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: {
        type: 'text',
        classes: ['cover-letter', 'demo-cover-letter'],
        content: `{proposal_cover_letter}`,
        style: {}
      },
    });
    (window as any).gjs_instance.BlockManager.add('proposal-expiry-date',{
      label: 'Expiry Date',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{proposal_expiry_date}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-sent-date',{
      label: 'Sent Date',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{proposal_sent_date}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-lease-term',{
      label: 'Lease Term',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{proposal_lease_term}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-lease-type',{
      label: 'Lease Type',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{proposal_lease_type}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-prints-inc-black',{
      label: 'Black Prints Included',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{black_prints_included}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-prints-inc-color',{
      label: 'Color Prints Included',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{color_prints_included}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-print-cost-black',{
      label: 'Black Overage Cost',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{black_overage_cost}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-print-cost-color',{
      label: 'Color Overage Cost',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{color_overage_cost}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-monthly-payment',{
      label: 'Monthly Payment',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{monthly_payment}</span>`,
    });
    (window as any).gjs_instance.BlockManager.add('proposal-onetime-payment',{
      label: 'One-time Payment',
      category: 'Proposal Details',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: `<span>{one_time_payment}</span>`,
    });

    // Shortcodes for Product Details
    (window as any).gjs_instance.BlockManager.add('product-page', {
      label: 'Product Page',
      category: 'Product Details',
      media: `<svg fill="#ffffff" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M86.45,23.27h-3.475V90.18c0,0.835-0.677,1.513-1.513,1.513H31.987v3.475c0,0.836,0.677,1.513,1.513,1.513l0.001,0v0h52.95 c0.836,0,1.513-0.677,1.513-1.513V24.782C87.963,23.946,87.286,23.27,86.45,23.27z"></path> <path d="M77.988,85.193V14.807c0-0.836-0.677-1.513-1.513-1.513h-3.475v66.911c0,0.836-0.677,1.513-1.513,1.513H22.011v3.475 c0,0.836,0.677,1.513,1.513,1.513c0,0,0,0,0,0h52.951C77.311,86.706,77.988,86.029,77.988,85.193z"></path> <path d="M68.013,75.218V4.832c0-0.836-0.677-1.513-1.513-1.513H13.55c-0.836,0-1.513,0.677-1.513,1.513v70.386 c0,0.836,0.677,1.513,1.513,1.513H66.5C67.336,76.731,68.013,76.054,68.013,75.218z"></path> </g> </g></svg>`,
      content: () => {
        const existingPages = (window as any).gjs_instance.getWrapper().find('.demo-product-page');
        if (existingPages.length > 0) {
          store.ShowError('You only need one "Product Page" component and it already exists!');
          return null;
        }
        const class_randomize = Date.now().toString(36);

        return {
          tagName: 'section',
          classes: ['pdf-page', 'demo-product-page',`pdf-page-${class_randomize}`],
          droppable: false,
          components: [
            {
              tagName: 'div',
              classes: ['product-page-content'],
              droppable: true,
              components: [],
              style: {
                height: '100%',
                width: '100%',
              }
            },
            {
              tagName: 'span',
              attributes: { 'data-demo-end-marker': '1' },
              removable: false,
              draggable: false,
              selectable: false,
              copyable: false,
              style: { display: 'none' }
            }
          ],
          style: {
            position: 'relative',
          }
        };
      },
    });
    (window as any).gjs_instance.BlockManager.add('product-title', {
      label: 'Product Title',
      category: 'Product Details',
      media: `<svg viewBox="0 0 24 24">
    <path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" />
  </svg>`,
      content: {
        type: 'text',
        classes: ['product-title'],
        content: `{product_title}`,
        droppable: false,
        components: [],
        style: {
          position: 'relative',
        },
      }
    });
    (window as any).gjs_instance.BlockManager.add('product-image', {
      label: 'Product Image',
      category: 'Product Details',
      media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zm-2 0H5V5h14zm-7-7a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm-5 6l3-4 2.5 3.01L17 13l4 6H5z"/></svg>`,
      content: {
        type: "text",
        classes: ['product-image'],
        droppable: false,
        components: [],
        content: `{product_image}`,
      }
    });
    (window as any).gjs_instance.BlockManager.add('product-specs', {
      label: 'Product Specs',
      category: 'Product Details',
      media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>`,
      content: {
        type: 'text',
        classes: ['product-specs'],
        content: `{product_specs}`,
        droppable: false,
        style: {
          width: '100%',
          'min-height': '200px',
        },
        components: [],
      }
    });
    (window as any).gjs_instance.BlockManager.add('product-description', {
      label: 'Product Description',
      category: 'Product Details',
      media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>`,
      content: {
        type: 'text',
        classes: ['product-description'],
        content: `{product_description}`,
        droppable: false,
        components: [],
        style: {
          position: 'relative',
        },
      }
    });

    // Product Line Items
    for (let counter=1; counter < 11; counter++) {
      (window as any).gjs_instance.BlockManager.add(`line-item-name-${counter}`, {
        label: `Product Name (Line Item ${counter})`,
        category: 'Line Items',
        media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>`,
        content: "<span>{item_"+counter+"_name}</span>",
      });
      (window as any).gjs_instance.BlockManager.add(`line-item-qty-${counter}`, {
        label: `Product Qty. (Line Item ${counter})`,
        category: 'Line Items',
        media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>`,
        content: "<span>{item_"+counter+"_qty}</span>",
      });
      (window as any).gjs_instance.BlockManager.add(`line-item-subtotal-${counter}`, {
        label: `Product Subtotal. (Line Item ${counter})`,
        category: 'Line Items',
        media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>`,
        content: "<span>{item_"+counter+"_subtotal}</span>",
      });
      (window as any).gjs_instance.BlockManager.add(`line-item-product-id-${counter}`, {
        label: `Product ID (Line Item ${counter})`,
        category: 'Line Items',
        media: `<svg viewBox="0 0 24 24"><path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>`,
        content: "<span>{item_"+counter+"_product_id}</span>",
      });
    }


    // Shortcodes for Summary Page
    (window as any).gjs_instance.BlockManager.add('summary-table',{
      label: 'Summary Table',
      category: 'Summary & Signature',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: {
        type: 'text',
        classes: ['demo-summary-table'],
        content: `{summary_table}`,
        droppable: false,
        components: [],
        style: {
          position: 'relative',
        },
      }
    });
    (window as any).gjs_instance.BlockManager.add('accept-button',{
      label: 'Accept Button',
      category: 'Summary & Signature',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: {
        type: 'text',
        classes: ['demo-summary-button'],
        content: `{summary_accept_button}`,
        droppable: false,
        components: [],
        style: {
          position: 'relative',
        },
      }
    });
    (window as any).gjs_instance.BlockManager.add('popup-trigger',{
      label: 'Popup Trigger',
      category: 'Summary & Signature',
      media: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M8 13H14M8 17H16M13 3H5V21H19V9M13 3H14L19 8V9M13 3V7C13 8 14 9 15 9H19" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>`,
      content: {
        droppable: true,
        tagName: 'div',
        type: 'text',
        style: {
          cursor: "pointer",
        },
        attributes: { onclick: 'window.AcceptProposal()' },
        content: 'Your content here'
      },
    });

    // Traits for Table Cells (Can be modified via SETTINGS)
    (window as any).gjs_instance.DomComponents.addType('table-cell', {
      extend: 'text',
      isComponent: (el:any) => el.tagName === 'TD' || el.tagName === 'TH', // Makes the HTML editable again
      model: {
        defaults: {
          editable: true,
          droppable: true,
          traits: [
            { type: 'number', name: 'colspan', label: 'Colspan', min: 1, value: 1 },
            { type: 'number', name: 'rowspan', label: 'Rowspan', min: 1, value: 1 },
          ],
          style: {},
        },
      },
    });

    // Custom Fields are populated inside watchEffect below
    watchEffect(() => {
      if (my_custom_fields.value.length && is_gjs_loaded.value) {
        // Only add if custom fields dont exists yet
        if (!are_customfields_added.value) {
          are_customfields_added.value = true;
          my_custom_fields.value.forEach((field) => {
            (window as any).gjs_instance.BlockManager.add(field.field_key, {
              label: field.field_label,
              category: 'Custom Fields',
              media: `<svg viewBox="0 0 24 24">
                      <path fill="currentColor" d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" />
                    </svg>`,
              content: {
                tagName: 'span',
                type: 'text',
                content: `{${field.field_key}}`,
                style: {},
                droppable: true,
              }
            });
          });
        }
      }
    });
  });
});

</script>
<style>
/*gui fixes*/
.gjs-devices-c { padding: 0 }
</style>
<style lang="scss" scoped>
// We need to isolate this scss to this file.
// as it is meant for demo purposes only.
@use "@/styles/grapesjs-demo.scss" as *;
</style>
