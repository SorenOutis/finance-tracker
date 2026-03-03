export const useLocalStorage = {
    setCurrentPage: (page: string) => {
        localStorage.setItem('current-page', page);
    },

    getCurrentPage: () => {
        return localStorage.getItem('current-page');
    },

    clearCurrentPage: () => {
        localStorage.removeItem('current-page');
    },

    saveTab: (key: string, tab: string) => {
        localStorage.setItem(`tab-${key}`, tab);
    },

    getTab: (key: string) => {
        return localStorage.getItem(`tab-${key}`);
    },

    saveModalState: (key: string, isOpen: boolean) => {
        localStorage.setItem(`modal-${key}`, isOpen ? 'true' : 'false');
    },

    getModalState: (key: string) => {
        const value = localStorage.getItem(`modal-${key}`);
        return value === 'true';
    },
};
