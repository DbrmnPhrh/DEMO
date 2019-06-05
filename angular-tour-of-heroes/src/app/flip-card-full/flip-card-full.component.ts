import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'nb-flip-card',
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './flip-card-full.component.html',
})
export class FlipCardFullComponent {
}