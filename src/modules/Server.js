export default class Server {
    token = '';
    mapHash = '';
    map = {}; 
    gamer = {};

    async sendRequest(method, data = {}) {
        data.method = method;
        data.token = this.token;
        let arr = [];
        Object.keys(data).forEach(key => arr.push(`${key}=${data[key]}`));
        const request = await fetch('http://stoneage/api/?' + arr.join('&'));
        const answer = await request.json();
        if (answer && answer.result === 'ok') {
            return answer.data;
        }
        return false;
    }

    async login(login, password) {
        if (login && password) {
            var md5 = require('md5');
            const num = Math.round(Math.random() * 100000);
            const hashPassword = md5(login + password);
            this.token = md5(hashPassword + num);
            this.token = await this.sendRequest('login', { login, hashPassword, num });
            if (this.token) {
                this.gamer = await this.sendRequest('join');
                if (this.gamer) {
                    localStorage.setItem('token', this.token);
                    return true;
                }
            }
        }
        return false;
    }

    async registration(nickname, login, password) {
        if (nickname && login && password) {
            var md5 = require('md5');
            const num = Math.round(Math.random() * 100000);
            const hashPassword = md5(login + password);
            this.token = md5(hashPassword + num);
            const checkToken =  await this.sendRequest('registration', { nickname, login, hashPassword, num });
            if (this.token === checkToken) {
                this.gamer = await this.sendRequest('join');
                if (this.gamer) {
                    console.log(this.gamer);
                    localStorage.setItem('token', this.token);
                    return true;
                }
            }    
        }
        return false;
    }

    async logout(token) {
        if (token) {
            this.token = token;
            const result = await this.sendRequest('logout');
            if (result) {
                this.token = '';
                this.map = {};
                this.gamer = {};
                localStorage.setItem('token', '');
            }
        }
    }

    async getMap() {
        return await this.sendRequest('getMap');
    }

    async checkHash () {
        if (this.token) {
            let hash = this.mapHash;
            const bdMapHash = await this.sendRequest ('updateMap', {hash});
            if(bdMapHash !== this.hash && bdMapHash !== false) {
                this.mapHash = bdMapHash;
                console.log('mapHash: ' + this.mapHash);
                this.map = await this.getMap();
                if (this.map) {
                    for (let i = 0; i < this.map.gamers.length; i++) {
                        if (this.map.gamers[i].id === this.gamer.id) {
                            this.gamer = this.map.gamers[i];
                            this.map.gamer = (this.gamer);
                            break;
                        }
                    }
                }
            }
            return this.map;
        }
    }

    move(direction) {
        return this.sendRequest('move', { direction });
    }

    takeItem() {
        return this.sendRequest('takeItem');
    }

    dropItem(hand) {
        return this.sendRequest('dropItem', { hand });
    }

    putOn() {
        return this.sendRequest('putOn');
    }

    putOnBackpack() {
        return this.sendRequest('putOnBackpack');
    }

    repair() {
        return this.sendRequest('repair');
    }

    fix() {
        return this.sendRequest('fix');
    }

    eat() {
        return this.sendRequest('eat');
    }

    makeItem() {
        return this.sendRequest('makeItem');
    }

    makeBuilding() {
        return this.sendRequest('makeBuilding');
    }

    keepBuilding() {
        return this.sendRequest('keepBuilding');
    }
}