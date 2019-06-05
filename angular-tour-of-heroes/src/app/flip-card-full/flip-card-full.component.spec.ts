import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FlipCardFullComponent } from './flip-card-full.component';

describe('FlipCardFullComponent', () => {
  let component: FlipCardFullComponent;
  let fixture: ComponentFixture<FlipCardFullComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FlipCardFullComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FlipCardFullComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
