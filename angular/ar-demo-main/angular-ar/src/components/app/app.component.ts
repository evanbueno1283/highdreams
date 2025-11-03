import { Component, HostListener, ChangeDetectorRef, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { BehaviorSubject } from 'rxjs';
import { VtoService } from 'src/services/vto-service/vto.service';

declare var WEBARROCKSHAND: any;

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements AfterViewInit {
  public shoeRightPath!: BehaviorSubject<string>;
  public mode: string = 'barefoot-vto';
  sidebarActive = false;

  constructor(
    private vtoService: VtoService,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) {
    this.shoeRightPath = vtoService.getShoeRightPath();
  }

  ngAfterViewInit(): void {
    // ✅ Force CSS repaint & change detection after DOM loads
    setTimeout(() => {
      this.cdr.detectChanges();
      document.body.style.display = 'block';
    }, 100);
  }

  toggleSidebar() {
    this.sidebarActive = !this.sidebarActive;
  }

  @HostListener('document:click', ['$event'])
  clickOutside(event: Event) {
    const sidebar = document.getElementById('sidebar');
    const menuBtn = document.querySelector('.menu-btn');

    if (
      this.sidebarActive &&
      sidebar &&
      !sidebar.contains(event.target as Node) &&
      menuBtn &&
      !menuBtn.contains(event.target as Node)
    ) {
      this.sidebarActive = false;
    }
  }

  swapShoe(shoeRightPath: string): void {
    this.shoeRightPath.next(shoeRightPath);
  }

  switchMode(path: string) {
    this.mode = path;
    if (typeof WEBARROCKSHAND !== 'undefined' && WEBARROCKSHAND.destroy) {
      WEBARROCKSHAND.destroy().then(() => {
        this.router.navigate(['/' + path]);
      });
    } else {
      this.router.navigate(['/' + path]);
    }
  }
}
