import { InMemoryDbService } from 'angular-in-memory-web-api';
import { Injectable } from '@angular/core';
import { Hero } from './hero';


@Injectable({
  providedIn: 'root'
})
export class  implements InMemoryDbService {
  createDb() {
    const heroes = [
      { id: 11, name: 'Mr. Nice' },
      { id: 12, name: 'Narco' },
      { id: 13, name: 'Bombasto' },
      { id: 14, name: 'Celeritas' },
      { id: 15, name: 'Magneta' },
      { id: 16, name: 'RubberMan' },
      { id: 17, name: 'Dynama' },
      { id: 18, name: 'Dr IQ' },
      { id: 19, name: 'Magma' },
      { id: 20, name: 'Tornado0' },
      // { id: 21, name: 'Tornado1' },
      // { id: 22, name: 'Tornado2' },
      // { id: 23, name: 'Tornado3' },
      // { id: 24, name: 'Tornado4' },
      // { id: 25, name: 'Tornado5' },
      // { id: 26, name: 'Tornado999' }
    ];
    return {heroes};
  }

  genId(heroes: Hero[]): number {
    return heroes.length > 0 ? Math.max(...heroes.map(hero => hero.id)) + 1 : 11;
  }

  constructor() { }
}
