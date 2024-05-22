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
    const me = Admin.getModule('manual') as modules.manual.admin.Manual;

    return new Aui.Panel({
        id: 'manuals-context',
        border: false,
        layout: 'column',
        iconClass: 'xi xi-tagged-book',
        title: (await me.getText('admin.contexts.manuals')) as string,
        scrollable: true,
        items: [
            new Aui.Grid.Panel({
                id: 'manuals',
                border: [false, true, false, false],
                width: 240,
                minWidth: 240,
                maxWidth: 400,
                resizable: [false, true, false, false],
                columnResizable: false,
                selection: { selectable: true },
                topbar: [
                    new Aui.Form.Field.Search({
                        name: 'keyword',
                        flex: 1,
                        emptyText: (await me.getText('keyword')) as string,
                        liveSearch: true,
                        handler: async (keyword, field) => {
                            const grid = field.getParent().getParent() as Aui.Grid.Panel;
                            if (keyword.length > 0) {
                                grid.getStore().setFilters(
                                    {
                                        title: { value: keyword, operator: 'likecode' },
                                        manual_id: { value: keyword, operator: 'likecode' },
                                    },
                                    'OR'
                                );
                            } else {
                                grid.getStore().resetFilters();
                            }
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.manuals.add')) as string,
                        handler: () => {
                            me.manuals.add();
                        },
                    }),
                ],
                bottombar: [
                    new Aui.Button({
                        iconClass: 'mi mi-refresh',
                        handler: (button) => {
                            const grid = button.getParent().getParent() as Aui.Grid.Panel;
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
                        text: (await me.getText('admin.manuals.title')) as string,
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
                        const categories = Aui.getComponent('categories') as Aui.Grid.Panel;
                        if (selections.length == 1) {
                            const manual_id = selections[0].get('manual_id');
                            categories.getStore().setParams({ manual_id: manual_id });
                            categories.getStore().reload();
                            categories.enable();

                            Aui.getComponent('manuals-context').properties.setUrl();
                        } else {
                            categories.disable();
                        }
                    },
                },
            }),
            new Aui.Grid.Panel({
                id: 'categories',
                border: [false, true, false, true],
                width: 240,
                minWidth: 240,
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
                        emptyText: (await me.getText('keyword')) as string,
                        liveSearch: true,
                        handler: async (keyword, field) => {
                            const grid = field.getParent().getParent() as Aui.Grid.Panel;
                            if (keyword.length > 0) {
                                grid.getStore().setFilters(
                                    {
                                        title: { value: keyword, operator: 'likecode' },
                                        category_id: { value: keyword, operator: 'likecode' },
                                    },
                                    'OR'
                                );
                            } else {
                                grid.getStore().resetFilters();
                            }
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.categories.add')) as string,
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
                            const grid = button.getParent().getParent() as Aui.Grid.Panel;
                            grid.moveSelections('up');

                            await grid.getStore().commit();
                            grid.restoreSelections();
                            button.setLoading(false);
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-caret-down',
                        handler: async (button) => {
                            const grid = button.getParent().getParent() as Aui.Grid.Panel;
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
                            const grid = button.getParent().getParent() as Aui.Grid.Panel;
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
                        text: (await me.getText('admin.categories.title')) as string,
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
                        const contents = Aui.getComponent('contents') as Aui.Grid.Panel;
                        if (selections.length == 1) {
                            const category_id = selections[0].get('category_id');
                            const manual_id = selections[0].get('manual_id');
                            const has_version = selections[0].get('has_version');
                            const versions = selections[0].get('versions');
                            contents.getStore().setParams({
                                category_id: category_id,
                                manual_id: manual_id,
                                has_version: has_version == true ? 'TRUE' : 'FALSE',
                                version: -1,
                            });
                            contents.getStore().reload();

                            const select = contents.getToolbar('bottom').getItemAt(5) as Aui.Form.Field.Select;
                            if (has_version == true) {
                                select.getStore().empty();
                                select.getStore().add(versions);
                                select.setValue(-1);
                                select.enable();
                                select.show();
                            } else {
                                select.disable();
                                select.hide();
                            }

                            contents.enable();

                            Aui.getComponent('manuals-context').properties.setUrl();
                        } else {
                            contents.disable();
                        }
                    },
                },
            }),
            new Aui.Tree.Panel({
                id: 'contents',
                border: [false, true, false, true],
                flex: 2,
                minWidth: 380,
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
                        emptyText: (await me.getText('keyword')) as string,
                        liveSearch: true,
                        handler: async (keyword, field) => {
                            const tree = field.getParent().getParent() as Aui.Tree.Panel;
                            if (keyword.length > 0) {
                                tree.getStore().setFilters(
                                    {
                                        title: { value: keyword, operator: 'likecode' },
                                    },
                                    'OR'
                                );
                            } else {
                                tree.getStore().resetFilters();
                            }
                        },
                    }),
                    '->',
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.contents.add')) as string,
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
                            const tree = button.getParent().getParent() as Aui.Tree.Panel;
                            tree.moveSelections('up');

                            await tree.getStore().commit();
                            tree.restoreSelections();
                            button.setLoading(false);
                        },
                    }),
                    new Aui.Button({
                        iconClass: 'mi mi-caret-down',
                        handler: async (button) => {
                            const tree = button.getParent().getParent() as Aui.Tree.Panel;
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
                            const tree = button.getParent().getParent() as Aui.Tree.Panel;
                            tree.getStore().reload();
                        },
                    }),
                    '->',
                    new Aui.Form.Field.Select({
                        width: 120,
                        store: new Aui.Store.Local({
                            fields: [{ name: 'value', type: 'int' }, 'display'],
                            records: [],
                        }),
                        valueField: 'value',
                        displayField: 'display',
                        listeners: {
                            change: (field, value) => {
                                const grid = field.getParent().getParent() as Aui.Grid.Panel;
                                if (grid.getStore().getParam('version') != value) {
                                    grid.getStore().setParam('version', value);
                                    grid.getStore().reload();
                                }
                            },
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
                        text: (await me.getText('admin.contents.title')) as string,
                        dataIndex: 'title',
                        flex: 1,
                    },
                    {
                        text: (await me.getText('admin.contents.documents')) as string,
                        dataIndex: 'documents',
                        width: 60,
                        textAlign: 'right',
                        renderer: (value) => {
                            return Format.number(value);
                        },
                    },
                    {
                        text: (await me.getText('admin.contents.hits')) as string,
                        dataIndex: 'hits',
                        width: 75,
                        textAlign: 'right',
                        renderer: (value) => {
                            return Format.number(value);
                        },
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
                    selectionChange: (selections, tree) => {
                        const documents = Aui.getComponent('documents') as Aui.Grid.Panel;
                        if (selections.length == 1) {
                            const manual_id = selections[0].get('manual_id');
                            const category_id = selections[0].get('category_id');
                            const content_id = selections[0].get('content_id');
                            const has_version = tree.getStore().getParam('has_version') == 'TRUE';
                            const version = tree.getStore().getParam('version') ?? -1;
                            documents.getStore().setParams({
                                manual_id: manual_id,
                                category_id: category_id,
                                content_id: content_id,
                                has_version: has_version == true ? 'TRUE' : 'FALSE',
                                version: version,
                            });
                            documents.getStore().reload();
                            documents.enable();

                            Aui.getComponent('manuals-context').properties.setUrl();
                        } else {
                            documents.disable();
                        }
                    },
                },
            }),
            new Aui.Grid.Panel({
                id: 'documents',
                border: [false, true, false, true],
                minWidth: 280,
                flex: 3,
                columnResizable: false,
                selection: { selectable: true },
                disabled: true,
                autoLoad: false,
                topbar: [
                    new Aui.Button({
                        iconClass: 'mi mi-plus',
                        text: (await me.getText('admin.documents.add')) as string,
                        handler: () => {
                            me.documents.add();
                        },
                    }),
                ],
                bottombar: [
                    new Aui.Button({
                        iconClass: 'mi mi-refresh',
                        handler: (button) => {
                            const grid = button.getParent().getParent() as Aui.Grid.Panel;
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
                        text: (await me.getText('admin.documents.start_version')) as string,
                        dataIndex: 'start_version',
                        textAlign: 'center',
                        width: 70,
                        renderer: (value) => {
                            if (value == -1) {
                                return '*';
                            }
                            return Math.floor(value / 1000) + '.' + (value % 1000);
                        },
                    },
                    {
                        text: (await me.getText('admin.documents.end_version')) as string,
                        dataIndex: 'end_version',
                        textAlign: 'center',
                        width: 70,
                        renderer: (value) => {
                            if (value == -1) {
                                return '*';
                            }
                            return Math.floor(value / 1000) + '.' + (value % 1000);
                        },
                    },
                    {
                        text: (await me.getText('admin.documents.author')) as string,
                        dataIndex: 'author',
                        minWidth: 120,
                        flex: 1,
                        renderer: (value) => {
                            return (
                                '<i class="photo" style="background-image:url(' + value.photo + ');"></i>' + value.name
                            );
                        },
                    },
                    {
                        text: (await me.getText('admin.documents.updated_at')) as string,
                        dataIndex: 'updated_at',
                        width: 145,
                        textAlign: 'center',
                        renderer: (value) => {
                            return Format.date('Y.m.d(D) H:i', value);
                        },
                    },
                    {
                        text: (await me.getText('admin.documents.hits')) as string,
                        dataIndex: 'hits',
                        width: 75,
                        textAlign: 'right',
                        renderer: (value) => {
                            return Format.number(value);
                        },
                    },
                ],
                listeners: {
                    load: (grid) => {
                        if (grid.getStore().getParam('has_version') == 'FALSE') {
                            grid.getColumnByIndex(0).setHidden(true);
                            grid.getColumnByIndex(1).setHidden(true);
                        }
                    },
                    openItem: (record) => {
                        me.documents.add(record.get('start_version'));
                    },
                    openMenu: (menu, record) => {
                        if (record.get('start_version') == -1) {
                            menu.setTitle(me.printText('admin.versions.all_version'));
                        } else {
                            menu.setTitle(
                                Math.floor(record.get('start_version') / 1000) +
                                    '.' +
                                    (record.get('start_version') % 1000)
                            );
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
            const manuals = Aui.getComponent('manuals') as Aui.Grid.Panel;
            const manual_id = manuals.getSelections().at(0)?.get('manual_id') ?? null;

            if (Admin.getContextSubUrl(0) !== manual_id) {
                Admin.setContextSubUrl('/' + manual_id);
            }

            const categories = Aui.getComponent('categories') as Aui.Grid.Panel;
            const category_id = categories.getSelections().at(0)?.get('category_id') ?? null;

            if (category_id !== null && Admin.getContextSubUrl(1) !== category_id) {
                Admin.setContextSubUrl('/' + manual_id + '/' + category_id);
            }

            const contents = Aui.getComponent('contents') as Aui.Tree.Panel;
            const content_id = contents.getSelections().at(0)?.get('content_id') ?? null;

            if (content_id !== null && Admin.getContextSubUrl(2) !== content_id) {
                Admin.setContextSubUrl('/' + manual_id + '/' + category_id + '/' + content_id);
            }
        },
    });
});
