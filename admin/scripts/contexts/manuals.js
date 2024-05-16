/**
 * 이 파일은 아이모듈 관리자모듈의 일부입니다. (https://www.imodules.io)
 *
 * 사이트관리화면을 구성한다.
 *
 * @file /modules/manual/admin/scripts/contexts/manuals.ts
 * @author Arzz <arzz@arzz.com>
 * @license MIT License
 * @modified 2024. 4. 23.
 */
Admin.ready(async () => {
    const me = Admin.getModule('manual');
    return new Aui.Panel({
        id: 'manuals-context',
        border: false,
        layout: 'column',
        iconClass: 'xi xi-tagged-book',
        title: (await me.getText('admin.contexts.manuals')),
        scrollable: true,
        items: [
            new Aui.Grid.Panel({
                id: 'manuals',
                border: [false, true, false, false],
                width: 280,
                minWidth: 280,
                maxWidth: 400,
                resizable: [false, true, false, false],
                columnResizable: false,
                selection: { selectable: true },
                topbar: [
                    new Aui.Form.Field.Search({
                        name: 'keyword',
                        flex: 1,
                        emptyText: (await me.getText('keyword')),
                        liveSearch: true,
                        handler: async (keyword, field) => {
                            const grid = field.getParent().getParent();
                            if (keyword.length > 0) {
                                grid.getStore().setFilters({
                                    title: { value: keyword, operator: 'likecode' },
                                    manual_id: { value: keyword, operator: 'likecode' },
                                }, 'OR');
                            }
                            else {
                                grid.getStore().resetFilters();
                            }
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.manuals.add')),
                        handler: () => {
                            me.manuals.add();
                        },
                    }),
                ],
                bottombar: [
                    new Aui.Button({
                        iconClass: 'mi mi-refresh',
                        handler: (button) => {
                            const grid = button.getParent().getParent();
                            grid.getStore().reload();
                        },
                    }),
                ],
                store: new Aui.Store.Remote({
                    url: me.getProcessUrl('manuals'),
                    primaryKeys: ['manual_id'],
                }),
                columns: [
                    {
                        text: (await me.getText('admin.manuals.title')),
                        dataIndex: 'title',
                    },
                ],
                listeners: {
                    update: (grid) => {
                        if (Admin.getContextSubUrl(0) !== null && grid.getSelections().length == 0) {
                            grid.select({ manual_id: Admin.getContextSubUrl(0) });
                            if (grid.getSelections().length == 0 && grid.getStore().getCount() > 0) {
                                grid.selectRow(0);
                            }
                        }
                    },
                    openItem: (record) => {
                        me.manuals.add(record.get('manual_id'));
                    },
                    openMenu: (menu, record) => {
                        menu.setTitle(record.get('title'));
                        menu.add({
                            text: me.printText('admin.manuals.edit'),
                            iconClass: 'mi mi-edit',
                            handler: async () => {
                                me.manuals.add(record.get('manual_id'));
                                return true;
                            },
                        });
                        menu.add({
                            text: me.printText('admin.manuals.delete'),
                            iconClass: 'mi mi-trash',
                            handler: async () => {
                                me.manuals.delete(record.get('manual_id'));
                                return true;
                            },
                        });
                    },
                    selectionChange: (selections) => {
                        const categories = Aui.getComponent('categories');
                        if (selections.length == 1) {
                            const manual_id = selections[0].get('manual_id');
                            categories.getStore().setParams({ manual_id: manual_id });
                            categories.getStore().reload();
                            categories.enable();
                            Aui.getComponent('manuals-context').properties.setUrl();
                        }
                        else {
                            categories.disable();
                        }
                    },
                },
            }),
            new Aui.Grid.Panel({
                id: 'categories',
                border: [false, true, false, true],
                width: 280,
                minWidth: 280,
                maxWidth: 400,
                resizable: [false, true, false, false],
                columnResizable: false,
                selection: { selectable: true },
                disabled: true,
                autoLoad: false,
                topbar: [
                    new Aui.Form.Field.Search({
                        name: 'keyword',
                        flex: 1,
                        emptyText: (await me.getText('keyword')),
                        liveSearch: true,
                        handler: async (keyword, field) => {
                            const grid = field.getParent().getParent();
                            if (keyword.length > 0) {
                                grid.getStore().setFilters({
                                    title: { value: keyword, operator: 'likecode' },
                                    category_id: { value: keyword, operator: 'likecode' },
                                }, 'OR');
                            }
                            else {
                                grid.getStore().resetFilters();
                            }
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.categories.add')),
                        handler: () => {
                            me.categories.add();
                        },
                    }),
                ],
                bottombar: [
                    new Aui.Button({
                        iconClass: 'mi mi-caret-up',
                        handler: async (button) => {
                            button.setLoading(true);
                            const grid = button.getParent().getParent();
                            grid.moveSelections('up');
                            await grid.getStore().commit();
                            grid.restoreSelections();
                            button.setLoading(false);
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-caret-down',
                        handler: async (button) => {
                            const grid = button.getParent().getParent();
                            grid.moveSelections('down');
                            await grid.getStore().commit();
                            grid.restoreSelections();
                            button.setLoading(false);
                        },
                    }),
                    '|',
                    new Aui.Button({
                        iconClass: 'mi mi-refresh',
                        handler: (button) => {
                            const grid = button.getParent().getParent();
                            grid.getStore().reload();
                        },
                    }),
                ],
                store: new Aui.Store.Remote({
                    url: me.getProcessUrl('categories'),
                    primaryKeys: ['category_id', 'manual_id'],
                    sorters: { sort: 'ASC' },
                }),
                columns: [
                    {
                        text: (await me.getText('admin.categories.title')),
                        dataIndex: 'title',
                        renderer: (value, record) => {
                            let sHTML = '';
                            if (record.get('has_version') == true) {
                                sHTML += '<b class="label">VER</b>';
                            }
                            sHTML += value;
                            if (record.get('permission') !== 'true') {
                                sHTML += '<i class="icon mi mi-lock"></i>';
                            }
                            return sHTML;
                        },
                    },
                ],
                listeners: {
                    update: (grid) => {
                        if (Admin.getContextSubUrl(1) !== null && grid.getSelections().length == 0) {
                            grid.select({
                                manual_id: Admin.getContextSubUrl(0),
                                category_id: Admin.getContextSubUrl(1),
                            });
                            if (grid.getSelections().length == 0 && grid.getStore().getCount() > 0) {
                                grid.selectRow(0);
                            }
                        }
                    },
                    openItem: (record) => {
                        me.categories.add(record.get('category_id'));
                    },
                    openMenu: (menu, record) => {
                        menu.setTitle(record.get('title'));
                        menu.add({
                            text: me.printText('admin.categories.edit'),
                            iconClass: 'mi mi-edit',
                            handler: async () => {
                                me.categories.add(record.get('category_id'));
                                return true;
                            },
                        });
                        menu.add({
                            text: me.printText('admin.categories.delete'),
                            iconClass: 'mi mi-trash',
                            handler: async () => {
                                me.categories.delete(record.get('category_id'));
                                return true;
                            },
                        });
                    },
                    selectionChange: (selections) => {
                        const contents = Aui.getComponent('contents');
                        if (selections.length == 1) {
                            const category_id = selections[0].get('category_id');
                            const manual_id = selections[0].get('manual_id');
                            contents.getStore().setParams({
                                category_id: category_id,
                                manual_id: manual_id,
                            });
                            contents.getStore().reload();
                            contents.enable();
                            Aui.getComponent('manuals-context').properties.setUrl();
                        }
                        else {
                            contents.disable();
                        }
                    },
                },
            }),
            new Aui.Tree.Panel({
                id: 'contents',
                border: [false, true, false, true],
                flex: 1,
                minWidth: 400,
                maxWidth: 500,
                resizable: [false, true, false, false],
                columnResizable: true,
                selection: { selectable: true },
                disabled: true,
                autoLoad: false,
                topbar: [
                    new Aui.Form.Field.Search({
                        name: 'keyword',
                        width: 200,
                        emptyText: (await me.getText('keyword')),
                        liveSearch: true,
                        handler: async (keyword, field) => {
                            const tree = field.getParent().getParent();
                            if (keyword.length > 0) {
                                tree.getStore().setFilters({
                                    context_id: { value: keyword, operator: 'likecode' },
                                    title: { value: keyword, operator: 'likecode' },
                                }, 'OR');
                            }
                            else {
                                tree.getStore().resetFilters();
                            }
                        },
                    }),
                    '->',
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.contents.add')),
                        handler: () => {
                            me.contents.add();
                        },
                    }),
                ],
                bottombar: [
                    new Aui.Button({
                        iconClass: 'mi mi-caret-up',
                        handler: async (button) => {
                            button.setLoading(true);
                            const tree = button.getParent().getParent();
                            tree.moveSelections('up');
                            await tree.getStore().commit();
                            tree.restoreSelections();
                            button.setLoading(false);
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-caret-down',
                        handler: async (button) => {
                            const tree = button.getParent().getParent();
                            tree.moveSelections('down');
                            await tree.getStore().commit();
                            tree.restoreSelections();
                            button.setLoading(false);
                        },
                    }),
                    '|',
                    new Aui.Button({
                        iconClass: 'mi mi-refresh',
                        handler: (button) => {
                            const tree = button.getParent().getParent();
                            tree.getStore().reload();
                        },
                    }),
                ],
                store: new Aui.TreeStore.Remote({
                    url: me.getProcessUrl('contents'),
                    primaryKeys: ['content_id'],
                    sorters: { sort: 'ASC' },
                }),
                columns: [
                    {
                        text: (await me.getText('admin.contents.title')),
                        dataIndex: 'title',
                        flex: 1,
                    },
                    {
                        text: (await me.getText('admin.contents.documents')),
                        dataIndex: 'documents',
                        width: 60,
                    },
                    {
                        text: (await me.getText('admin.contents.hits')),
                        dataIndex: 'hits',
                        width: 75,
                    },
                ],
                listeners: {
                    update: (tree) => {
                        if (Admin.getContextSubUrl(2) !== null && tree.getSelections().length == 0) {
                            tree.select({
                                manual_id: Admin.getContextSubUrl(0),
                                category_id: Admin.getContextSubUrl(1),
                                content_id: Admin.getContextSubUrl(2),
                            });
                            if (tree.getSelections().length == 0 && tree.getStore().getCount() > 0) {
                                tree.selectRow([0]);
                            }
                        }
                    },
                    openItem: (record) => {
                        me.contents.add(record.get('content_id'));
                    },
                    openMenu: (menu, record) => {
                        menu.setTitle(record.get('title'));
                        menu.add({
                            text: me.printText('admin.contents.edit'),
                            iconClass: 'mi mi-edit',
                            handler: async () => {
                                me.contents.add(record.get('content_id'));
                                return true;
                            },
                        });
                        menu.add({
                            text: me.printText('admin.contents.delete'),
                            iconClass: 'mi mi-trash',
                            handler: async () => {
                                me.contents.delete(record.get('content_id'));
                                return true;
                            },
                        });
                    },
                    selectionChange: (selections) => {
                        const documents = Aui.getComponent('documents');
                        if (selections.length == 1) {
                            const manual_id = selections[0].get('manual_id');
                            const category_id = selections[0].get('category_id');
                            const content_id = selections[0].get('content_id');
                            documents.getStore().setParams({
                                manual_id: manual_id,
                                category_id: category_id,
                                content_id: content_id,
                            });
                            documents.getStore().reload();
                            documents.enable();
                            Aui.getComponent('manuals-context').properties.setUrl();
                        }
                        else {
                            documents.disable();
                        }
                    },
                },
            }),
            new Aui.Grid.Panel({
                id: 'documents',
                border: [false, true, false, true],
                minWidth: 300,
                flex: 1,
                columnResizable: false,
                selection: { selectable: true },
                disabled: true,
                autoLoad: false,
                topbar: [
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.documents.add')),
                        handler: () => {
                            me.documents.add();
                        },
                    }),
                ],
                bottombar: [
                    new Aui.Button({
                        iconClass: 'mi mi-refresh',
                        handler: (button) => {
                            const grid = button.getParent().getParent();
                            grid.getStore().reload();
                        },
                    }),
                ],
                store: new Aui.Store.Remote({
                    url: me.getProcessUrl('documents'),
                    fields: [
                        { name: 'start_version', type: 'int' },
                        { name: 'end_version', type: 'int' },
                    ],
                    primaryKeys: ['start_version'],
                    sorters: { start_version: 'DESC' },
                }),
                columns: [
                    {
                        text: (await me.getText('admin.documents.start_version')),
                        dataIndex: 'start_version',
                        textAlign: 'center',
                        width: 70,
                        renderer: (value) => {
                            return Math.floor(value / 1000) + '.' + (value % 1000);
                        },
                    },
                    {
                        text: (await me.getText('admin.documents.end_version')),
                        dataIndex: 'end_version',
                        textAlign: 'center',
                        width: 70,
                        renderer: (value) => {
                            return Math.floor(value / 1000) + '.' + (value % 1000);
                        },
                    },
                    {
                        text: (await me.getText('admin.documents.author')),
                        dataIndex: 'author',
                        minWidth: 120,
                        flex: 1,
                        renderer: (value) => {
                            return ('<i class="photo" style="background-image:url(' + value.photo + ');"></i>' + value.name);
                        },
                    },
                    {
                        text: (await me.getText('admin.documents.hits')),
                        dataIndex: 'hits',
                        width: 75,
                        textAlign: 'right',
                        renderer: (value) => {
                            return Format.number(value);
                        },
                    },
                ],
                listeners: {
                    openItem: (record) => {
                        me.documents.add(record.get('start_version'));
                    },
                    openMenu: (menu, record) => {
                        if (record.get('start_version') == -1) {
                            menu.setTitle(me.printText('admin.versions.all_version'));
                        }
                        else {
                            menu.setTitle(Math.floor(record.get('start_version') / 1000) +
                                '.' +
                                (record.get('start_version') % 1000));
                        }
                        menu.add({
                            text: me.printText('admin.documents.edit'),
                            iconClass: 'mi mi-edit',
                            handler: async () => {
                                me.documents.add(record.get('start_version'));
                                return true;
                            },
                        });
                        menu.add({
                            text: me.printText('admin.documents.delete'),
                            iconClass: 'mi mi-trash',
                            handler: async () => {
                                me.documents.delete(record.get('start_version'));
                                return true;
                            },
                        });
                    },
                },
            }),
        ],
        setUrl: () => {
            const manuals = Aui.getComponent('manuals');
            const manual_id = manuals.getSelections().at(0)?.get('manual_id') ?? null;
            if (Admin.getContextSubUrl(0) !== manual_id) {
                Admin.setContextSubUrl('/' + manual_id);
            }
            const categories = Aui.getComponent('categories');
            const category_id = categories.getSelections().at(0)?.get('category_id') ?? null;
            if (category_id !== null && Admin.getContextSubUrl(1) !== category_id) {
                Admin.setContextSubUrl('/' + manual_id + '/' + category_id);
            }
            const contents = Aui.getComponent('contents');
            const content_id = contents.getSelections().at(0)?.get('content_id') ?? null;
            if (content_id !== null && Admin.getContextSubUrl(2) !== content_id) {
                Admin.setContextSubUrl('/' + manual_id + '/' + category_id + '/' + content_id);
            }
        },
    });
});
