#include "RoadIntersection.h"
#include "RoadSegment.h"
#include "Components/SplineComponent.h"

ARoadIntersection::ARoadIntersection()
{
    IntersectionType = EIntersectionType::Crossroad;
    NumRoads = 0;
}

void ARoadIntersection::AddRoad(ARoadSegment* Road, int32 ConnectionIndex)
{
    if (!Road)
    {
        return;
    }
    if (ConnectionIndex >= 0 && ConnectedRoads.IsValidIndex(ConnectionIndex))
    {
        ConnectedRoads[ConnectionIndex].ConnectedActor = Road;
    }
    else
    {
        FRoadConnection Connection;
        Connection.ConnectedActor = Road;
        ConnectionIndex = ConnectedRoads.Add(Connection);
    }
    UpdateConnection(ConnectionIndex, Road);
    GenerateIntersectionGeometry();
    ConfigureTrafficRules();
}

void ARoadIntersection::GenerateIntersectionGeometry()
{
    FVector Center = ComputeIntersectionCenter();
    SetActorLocation(Center);
    for (int32 Index = 0; Index < ConnectedRoads.Num(); ++Index)
    {
        ARoadSegment* Road = Cast<ARoadSegment>(ConnectedRoads[Index].ConnectedActor);
        UpdateConnection(Index, Road);
    }
    NumRoads = ConnectedRoads.Num();
}

void ARoadIntersection::ConfigureTrafficRules()
{
    TrafficRouting.Empty();
    for (int32 Index = 0; Index < ConnectedRoads.Num(); ++Index)
    {
        ARoadSegment* FromRoad = Cast<ARoadSegment>(ConnectedRoads[Index].ConnectedActor);
        if (!FromRoad)
        {
            continue;
        }
        TArray<TWeakObjectPtr<ARoadSegment>>& Routes = TrafficRouting.FindOrAdd(FromRoad);
        Routes.Reset();
        for (int32 OtherIndex = 0; OtherIndex < ConnectedRoads.Num(); ++OtherIndex)
        {
            if (Index == OtherIndex)
            {
                continue;
            }
            ARoadSegment* ToRoad = Cast<ARoadSegment>(ConnectedRoads[OtherIndex].ConnectedActor);
            if (ToRoad)
            {
                Routes.Add(ToRoad);
            }
        }
    }
}

void ARoadIntersection::UpdateConnection(int32 Index, ARoadSegment* Road)
{
    if (!ConnectedRoads.IsValidIndex(Index))
    {
        return;
    }
    FRoadConnection& Connection = ConnectedRoads[Index];
    Connection.Location = GetActorLocation();
    Connection.Rotation = FRotator::ZeroRotator;
    Connection.ConnectedActor = Road;
    if (!Road)
    {
        return;
    }
    USplineComponent* Spline = Road->GetSplineComponent();
    if (!Spline)
    {
        return;
    }
    const int32 PointCount = Spline->GetNumberOfSplinePoints();
    if (PointCount == 0)
    {
        return;
    }
    const int32 StartIndex = 0;
    const int32 EndIndex = PointCount - 1;
    const FVector StartLocation = Spline->GetLocationAtSplinePoint(StartIndex, ESplineCoordinateSpace::World);
    const FVector EndLocation = Spline->GetLocationAtSplinePoint(EndIndex, ESplineCoordinateSpace::World);
    const float StartDist = FVector::DistSquared(StartLocation, GetActorLocation());
    const float EndDist = FVector::DistSquared(EndLocation, GetActorLocation());
    const int32 TargetIndex = StartDist <= EndDist ? StartIndex : EndIndex;
    Spline->SetLocationAtSplinePoint(TargetIndex, GetActorLocation(), ESplineCoordinateSpace::World, false);
    Spline->UpdateSpline();
    Connection.Location = GetActorLocation();
    Connection.Rotation = Spline->GetRotationAtSplinePoint(TargetIndex, ESplineCoordinateSpace::World);
}

FVector ARoadIntersection::ComputeIntersectionCenter() const
{
    FVector Sum = FVector::ZeroVector;
    int32 Count = 0;
    const FVector CurrentLocation = GetActorLocation();
    for (const FRoadConnection& Connection : ConnectedRoads)
    {
        const ARoadSegment* Road = Cast<ARoadSegment>(Connection.ConnectedActor);
        if (!Road)
        {
            continue;
        }
        const USplineComponent* Spline = Road->GetSplineComponent();
        if (!Spline)
        {
            continue;
        }
        const int32 PointCount = Spline->GetNumberOfSplinePoints();
        if (PointCount == 0)
        {
            continue;
        }
        const FVector StartLocation = Spline->GetLocationAtSplinePoint(0, ESplineCoordinateSpace::World);
        const FVector EndLocation = Spline->GetLocationAtSplinePoint(PointCount - 1, ESplineCoordinateSpace::World);
        const FVector Reference = Connection.Location.IsNearlyZero() ? CurrentLocation : Connection.Location;
        const float StartDist = FVector::DistSquared(StartLocation, Reference);
        const float EndDist = FVector::DistSquared(EndLocation, Reference);
        const FVector Location = StartDist <= EndDist ? StartLocation : EndLocation;
        Sum += Location;
        ++Count;
    }
    return Count > 0 ? Sum / Count : CurrentLocation;
}
