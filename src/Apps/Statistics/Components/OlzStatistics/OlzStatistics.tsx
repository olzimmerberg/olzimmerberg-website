import React from 'react';
import {olzApi} from '../../../../Api/client';

import './OlzStatistics.scss';

const monthIdents = ['current'];
const date = new Date();
let year = date.getFullYear();
let month = date.getMonth() + 1;
for (let i = 0; i < 12; i++) {
    const prettyMonth = `${month}`.padStart(2, '0');
    monthIdents.push(`${year}-${prettyMonth}`);
    month--;
    if (month < 1) {
        month = 12;
        year--;
    }
}
for (let i = 0; i < 5; i++) {
    const prettyMonth = `${month}`.padStart(2, '0');
    monthIdents.push(`${year}-${prettyMonth}`);
    year--;
}

function getOlzStatisticsUrl(username: string, password: string): string {
    return 'https://olzimmerberg.ch/plesk-stat/webstat-ssl/';
}

export const OlzStatistics = () => {
    const [username, setUsername] = React.useState<string>('');
    const [password, setPassword] = React.useState<string>('');
    const [monthIdent, setMonthIdent] = React.useState<string>('current');

    React.useEffect(() => {
        olzApi.call('getAppStatisticsCredentials', {}).then((data) => {
            setUsername(data.username);
            setPassword(data.password);
        });
    }, []);

    const onMonthIdentChange = React.useCallback((e) => {
        const select = e.target;
        const newMonthIdent = select.options[select.selectedIndex].value;
        setMonthIdent(newMonthIdent);
    }, []);

    if (!username || !password) {
        return (
            <div>Lädt...</div>
        );
    }

    const statisticsUrl = getOlzStatisticsUrl(username, password);

    const options = monthIdents.map(value => (
        <option value={value} selected={value === monthIdent}>{value}</option>
    ));

    return (<>
        <div className='statistics-header'>
            <select className='form-control form-select form-select-sm' onChange={onMonthIdentChange}>
                {options}
            </select>
        </div>
        <iframe
            className='statistics-iframe'
            src={`${statisticsUrl}${monthIdent}/index.html`}
        >
        </iframe>
    </>);
};
